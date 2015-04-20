<?php
/**
*
*/

namespace Charcoal\Metadata;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Loader\FileLoader as FileLoader;

/**
*
*/
class MetadataLoader extends FileLoader
{

    private $_ident = '';

    /**
    * @param string $ident
    * @throws \InvalidArgumentException if the ident is not a string
    * @return MetadataLoader (Chainable)
    */
    public function set_ident($ident)
    {
        if(!is_string($ident)) {
            throw new \InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Ident must be a string.');
        }
        $this->_ident = $ident;
        return $this;
    }

    public function ident()
    {
        return $this->_ident;
    }

    /**
    * Get the object's search path, merged with global configuration path
    * @return array
    */
    public function search_path()
    {
        $cfg = Charcoal::config();

        $all_path = parent::search_path();

        $global_path = Charcoal::config()->metadata_path();
        if(!empty($global_path)) {
            $all_path = Charcoal::merge($global_path, $all_path);
        }
        return $all_path;
    }

    /**
    *
    */
    public function load($ident=null)
    {
        if($ident !== null) {
            $this->set_ident($ident);
        }

        // Attempt loading from cache
        $ret = $this->cache_load();
        if($ret !== false) {
            return $ret;
        }

        $hierarchy = $this->_hierarchy();

        $metadata = [];
        foreach($hierarchy as $id) {
            $ident_data = self::_load_ident($id);
            if(is_array($ident_data)) {
                $metadata = Charcoal::merge($metadata, $ident_data);
            }
        }

        $this->set_content($metadata);
        $this->cache_store();

        return $metadata;
    }

    /**
    * @return array
    */
    private function _hierarchy()
    {
        $ident = $this->ident();
        $hierarchy = null;

        if(class_exists($ident)) {
            // If the object is a class, we use hierarchy from object ancestor classes
            //  pre('=='.$ident);
            $p = $ident;
            $ident_hierarchy = [$p];

            // Also load class' traits, if any
            $traits = class_uses($ident);
            foreach($traits as $trait) {
                $ident_hierarchy[] = $trait;
            }
            //pre($p);
            while($p = get_parent_class($p)) {
                $ident_hierarchy[] = $p;

                // Also load parent classes' traits, if any
                $traits = class_uses($p);
                foreach($traits as $trait) {
                    //pre($trait);
                    $ident_hierarchy[] = $trait;
                }
            }
            
            $ident_hierarchy = array_reverse($ident_hierarchy);
        }
        else {
            if(is_array($hierarchy) && !empty($hierarchy)) {
                $hierarchy[] = $ident;
                $ident_hierarchy = $hierarchy;
            }
            else {
                $ident_hierarchy = [$ident];
            }
        }

        return $ident_hierarchy;
    }

    /**
    * Get an "ident" (file) from all search path and merge the content
    *
    * @param string $ident
    *
    * @return array
    */
    private function _load_ident($ident)
    {
        $data = [];
        $filename = $this->_filename_from_ident($ident);
        $files = $this->_all_matching_filenames($filename);
        foreach($files as $f) {
            $file_content = file_get_contents($f);
            if($file_content === '') {
                continue;
            }
            // Decode as an array (2nd parameter, true = array)
            $file_data = json_decode($file_content, true);
            // Todo: Handle json_last_error()
            if(is_array($file_data)) {
                $data = Charcoal::merge($data, $file_data);
            }
        }
        return $data;
    }

    /**
    * @param string
    *
    * @return string
    */
    private function _filename_from_ident($ident)
    {
        $filename = str_replace(['\\'], '.', $ident);
        $filename .= '.json';

        return $filename;

    }
}