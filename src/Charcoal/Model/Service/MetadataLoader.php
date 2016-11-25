<?php

namespace Charcoal\Model\Service;

// PHP dependencies
use \InvalidArgumentException;

// Dependencies from PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Dependencies from `PSR-6`
use \Psr\Cache\CacheItemPoolInterface;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\MetadataInterface;

/**
 * Load metadata from JSON file(s).
 *
 * The Metadata Loader is different than the `FileLoader` class it extends mainly because
 * it tries to find all files matching  the "ident" in all search path and merge them together
 * in an array, to be filled in a `Metadata` object.
 *
 * If `ident` is an actual class name, then it will also try to load all the JSON matching
 * the class' parents and interfaces.
 */
final class MetadataLoader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The PSR-6 caching service.
     *
     * @var CacheItemPoolInterface $cachePool
     */
    private $cachePool;

    /**
     * The cache of class/interface lineages.
     *
     * @var array
     */
    private static $hierarchyCache = [];

    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    private static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    private static $camelCache = [];

    /**
     * The base path to prepend to any relative paths to search in.
     *
     * @var string $basePath
     */
    private $basePath = '';

    /**
     * The paths to search in.
     *
     * @var string[] $paths
     */
    private $paths = [];


    /**
     * Return new MetadataLoader object.
     *
     * The application's metadata paths, if any, are merged with
     * the loader's search paths.
     *
     * # Required dependencie
     * - `logger`
     * - `cache`
     * - `paths`
     * - `base_path`
     *
     * @param array $data The loader's dependencies.
     */
    public function __construct(array $data = null)
    {
        $this->setLogger($data['logger']);
        $this->setCachePool($data['cache']);
        $this->setBasePath($data['base_path']);
        $this->setPaths($data['paths']);
    }

    /**
     * Load the metadata from JSON files.
     *
     * @param string            $ident    The metadata ident to load.
     * @param MetadataInterface $metadata The metadata object to load into.
     * @return array
     */
    public function load($ident, MetadataInterface $metadata)
    {
        $cacheKey = str_replace('/', '.', 'metadata/object/'.$ident);
        $cacheItem = $this->cachePool()->getItem($cacheKey);

        $obj = $cacheItem->get();
        if (!$cacheItem->isHit()) {
            $data = $this->loadData($ident);
            $metadata->setData($data);

            $this->cachePool()->save($cacheItem->set($metadata));
            $obj = $metadata;
        }

        return $obj;
    }

    /**
     * Load the metadata from JSON files.
     *
     * @param string $ident Optional, set the ident to load.
     * @return array
     */
    public function loadData($ident = null)
    {
        $hierarchy = $this->hierarchy($ident);

        $metadata = [];
        foreach ($hierarchy as $id) {
            $identData = $this->loadIdent($id);

            if (is_array($identData)) {
                $metadata = array_replace_recursive($metadata, $identData);
            }
        }

        return $metadata;
    }


    /**
     * @param CacheItemPoolInterface $cache A PSR-6 compliant cache pool instance.
     * @return MetadataLoader Chainable
     */
    private function setCachePool(CacheItemPoolInterface $cache)
    {
        $this->cachePool = $cache;
        return $this;
    }

    /**
     * @return CacheItemPoolInterface
     */
    private function cachePool()
    {
        return $this->cachePool;
    }

    /**
     * Assign a base path for relative search paths.
     *
     * @param  string $basePath The base path to use.
     * @throws InvalidArgumentException If the base path parameter is not a string.
     * @return MetadataLoader Chainable
     */
    private function setBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new InvalidArgumentException(
                'Base path must be a string'
            );
        }

        $basePath = realpath($basePath);
        $this->basePath = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Retrieve the base path for relative search paths.
     *
     * @return string
     */
    private function basePath()
    {
        return $this->basePath;
    }

    /**
     * Assign a list of paths.
     *
     * @param  string[] $paths The list of paths to add.
     * @return self
     */
    private function setPaths(array $paths)
    {
        $this->paths = [];
        $this->addPaths($paths);

        return $this;
    }

    /**
     * Retrieve the searchable paths.
     *
     * @return string[]
     */
    private function paths()
    {
        return $this->paths;
    }

    /**
     * Append a list of paths.
     *
     * @param  string[] $paths The list of paths to add.
     * @return self
     */
    private function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * Append a path.
     *
     * @param  string $path A file or directory path.
     * @throws InvalidArgumentException If the path does not exist or is invalid.
     * @return self
     */
    private function addPath($path)
    {
        $path = $this->resolvePath($path);

        if ($this->validatePath($path)) {
            $this->paths[] = $path;
        }

        return $this;
    }

    /**
     * Parse a relative path using the base path if needed.
     *
     * @param  string $path The path to resolve.
     * @throws InvalidArgumentException If the path is invalid.
     * @return string
     */
    private function resolvePath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }

        $basePath = $this->basePath();
        $path = ltrim($path, '/\\');

        if ($basePath && strpos($path, $basePath) === false) {
            $path = $basePath.$path;
        }

        return $path;
    }

    /**
     * Validate a resolved path.
     *
     * @param  string $path The path to validate.
     * @return string
     */
    private function validatePath($path)
    {
        return file_exists($path);
    }

    /**
     * Build a class/interface lineage from the given fully qualified class name (in snake case).
     *
     * @param  string $ident The FQCN (in snake-case) to load the hierarchy from.
     * @return array
     */
    private function hierarchy($ident)
    {
        if (!is_string($ident)) {
            return [];
        }

        if (isset(static::$hierarchyCache[$ident])) {
            return static::$hierarchyCache[$ident];
        }

        $classname = $this->identToClassname($ident);

        if (!class_exists($classname)) {
            return [ $ident ];
        }

        $classes   = array_values(class_parents($classname));
        $classes   = array_reverse($classes);
        $classes[] = $classname;

        $hierarchy = [];
        foreach ($classes as $class) {
            $implements = array_values(class_implements($class));
            $implements = array_reverse($implements);
            foreach ($implements as $interface) {
                $hierarchy[$this->classnameToIdent($interface)] = $interface;
            }
            $hierarchy[$this->classnameToIdent($class)] = $class;
        }

        $hierarchy = array_keys($hierarchy);

        static::$hierarchyCache[$ident] = $hierarchy;

        return $hierarchy;
    }

    /**
     * Get an "ident" (file) from all search path and merge the content
     *
     * @param string $ident The identifier from which to retrieve a file.
     * @throws InvalidArgumentException If a JSON decoding error occurs.
     * @return array|null
     */
    private function loadIdent($ident)
    {
        $name = $this->filenameFromIdent($ident);

        if (file_exists($name)) {
            return $this->loadJsonFile($name);
        }

        $paths = $this->paths();

        if (empty($paths)) {
            return null;
        }

        foreach ($paths as $path) {
            $file = $path.DIRECTORY_SEPARATOR.$name;
            if (file_exists($file)) {
                return $this->loadJsonFile($file);
            }
        }

        return null;
    }

        /**
         * Load the contents of a JSON file.
         *
         * @param  mixed $filename The file path to retrieve.
         * @throws InvalidArgumentException If a JSON decoding error occurs.
         * @return array|null
         */
    protected function loadJsonFile($filename)
    {
        $content = file_get_contents($filename);

        if ($content === null) {
            return null;
        }

        $data  = json_decode($content, true);
        $error = json_last_error();

        if ($error == JSON_ERROR_NONE) {
            return $data;
        }

        switch ($error) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $issue = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $issue = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $issue = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $issue = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $issue = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $issue = 'Unknown error';
                break;
        }

        throw new InvalidArgumentException(
            sprintf('JSON %s could not be parsed: "%s"', $filename, $issue)
        );
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    private function filenameFromIdent($ident)
    {
        $filename  = str_replace([ '\\' ], '.', $ident);
        $filename .= '.json';

        return $filename;
    }

    /**
     * Convert an identifier to a FQN.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    private function identToClassname($ident)
    {
        $key = $ident;

        if (isset(static::$camelCache[$key])) {
            return static::$camelCache[$key];
        }

        // Change "foo-bar" to "fooBar"
        $parts = explode('-', $ident);
        array_walk(
            $parts,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $ident = implode('', $parts);

        // Change "/foo/bar" to "\Foo\Bar"
        $classname = str_replace('/', '\\', $ident);
        $parts     = explode('\\', $classname);

        array_walk(
            $parts,
            function(&$i) {
                $i = ucfirst($i);
            }
        );

        $classname = trim(implode('\\', $parts), '\\');

        static::$camelCache[$key]       = $classname;
        static::$snakeCache[$classname] = $key;

        return $classname;
    }

    /**
     * Convert a FQN to an identifier.
     *
     * @param string $classname The FQN to convert.
     * @return string
     */
    private function classnameToIdent($classname)
    {
        $key = trim($classname, '\\');

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        $ident = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $classname));
        $ident = str_replace('\\', '/', strtolower($ident));
        $ident = ltrim($ident, '/');

        static::$snakeCache[$key]   = $ident;
        static::$camelCache[$ident] = $key;

        return $ident;
    }
}