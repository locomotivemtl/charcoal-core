<?php

namespace Charcoal;

use Closure;
use RuntimeException;

// From PSR-11
use Interop\Container\ContainerInterface;

// From 'charcoal-factory'
use Charcoal\Factory\GenericResolver as ClassResolver;

// From 'charcoal-core'
use Charcoal\CallableResolverInterface;

/**
 * Resolves a callable reference in the context of a class.
 *
 * This class resolves a string of the format 'class:method' into a closure
 * that can be dispatched.
 */
class CallableResolver implements CallableResolverInterface
{
    const CALLABLE_PATTERN = '!^([^\:]+)?\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var callable
     */
    private $classResolver;

    /**
     * @param array $data Resolver dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['container'])) {
            $this->setContainer($data['container']);
        }

        if (!isset($data['class_resolver'])) {
            $opts = isset($data['class_resolver_options']) ? $data['class_resolver_options'] : null;
            $data['class_resolver'] = new GenericResolver($opts);
        }

        $this->setClassResolver($data['class_resolver']);
    }

    /**
     * @param ContainerInterface $container The service container.
     * @return self
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return ContainerInterface|null
     */
    protected function container()
    {
        return $this->container;
    }

    /**
     * @param  callable $resolver The class resolver instance to use.
     * @return self
     */
    private function setClassResolver(callable $resolver)
    {
        $this->classResolver = $resolver;

        return $this;
    }

    /**
     * @return callable|null
     */
    protected function classResolver()
    {
        return $this->classResolver;
    }

    /**
     * Resolve the given value into a closure that can be dispatched.
     *
     * If $toResolve is of the format 'class:method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * If $scope is provided and $toResolve is of the format ':method' or contains
     * a special keyword (e.g. self, parent, and static), the resolver will lookup
     * 'method' on the $scope.
     *
     * @param  mixed       $toResolve A callable reference.
     * @param  object|null $scope     Optional object from which $toResolve could be found.
     * @throws RuntimeException If the callable does not exist.
     * @throws RuntimeException If the callable is not resolvable.
     * @return callable Returns the resolved reference.
     */
    public function resolve($toResolve, $scope = null)
    {
        if ($this->classResolver !== null) {
            if (is_string($toResolve)) {
                $resolver = $this->classResolver();
                $resolved = $resolver($toResolve);
            }
        }

        if (is_callable($toResolve)) {
            return $toResolve;
        }

        if (!is_string($toResolve)) {
            $this->assertCallable($toResolve);
        }

        // Check for callable as "class:method"
        if (preg_match(self::CALLABLE_PATTERN, $toResolve, $matches)) {
            if ($scope !== null &&
                in_array($matches[1], [ '', 'self', 'static', 'parent', get_class($scope) ])) {
                $this->assertCallableScope($scope);
            }

            $resolved = $this->resolveCallable($matches[1], $matches[2], $scope);
            $this->assertCallable($resolved);

            return $resolved;
        }

        $resolved = $this->resolveCallable($toResolve);
        $this->assertCallable($resolved);

        return $resolved;
    }

    /**
     * Check if string is something in the DIC
     * that's callable or is a class name which has an __invoke() method.
     *
     * @param  string $class  The class name.
     * @param  string $method The method name.
     * @throws RuntimeException if the callable does not exist
     * @return callable
     */
    protected function resolveCallable($class, $method = '__invoke', $scope = null)
    {
        if ($scope !== null &&
            in_array($class, [ '', 'self', 'static', 'parent', get_class($scope) ])) {
            $this->assertCallableScope($scope);

            switch ($class) {
                case '':
                case 'self':
                case 'static':
                    return [ $scope, $method ];

                case 'parent':
                    return [ $scope, 'parent::'.$method ];
            }
        }

        if ($this->container->has($class)) {
            return [ $this->container->get($class), $method ];
        }

        if (!class_exists($class)) {
            throw new RuntimeException(sprintf('Callable %s does not exist', $class));
        }

        return [ new $class($this->container), $method ];
    }

    /**
     * Asserts that the callable is valid, throws an Exception if not.
     *
     * @param  callable $callable Callable to test.
     * @throws RuntimeException if the callable is not resolvable.
     * @return void
     */
    protected function assertCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf(
                '%s is not resolvable',
                is_array($callable) || is_object($callable) ? json_encode($callable) : $callable
            ));
        }
    }

    /**
     * Asserts that the scope is valid, throws an Exception if not.
     *
     * @param  object $scope Scope to test.
     * @throws RuntimeException if the scope is not resolvable.
     * @return void
     */
    protected function assertCallableScope($scope)
    {
        if (!is_object($scope)) {
            throw new RuntimeException(sprintf(
                '%s is not an object',
                gettype($scope)
            ));
        }
    }
}