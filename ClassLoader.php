<?php

namespace Zoop\Slipstream;

/**
 * ClassLoader implements a PSR-0 class loader
 *
 * This class is a fork of the composer autoloader
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Tim Roediger <superdweebie@gmail.com>
 */
class ClassLoader
{
    private $prefixes = array();
    private $fallbackDirs = array();
    private $useIncludePath = false;
    private $classMap = array();
    private $classMapModified = false;
    private $destructCallbacks = array();

    public function getPrefixes()
    {
        return $this->prefixes;
    }

    public function getFallbackDirs()
    {
        return $this->fallbackDirs;
    }

    public function getClassMap()
    {
        return $this->classMap;
    }

    public function getClassMapModified()
    {
        return $this->classMapModified;
    }

    public function addDestructCallback($destructCallback) {
        $this->destructCallbacks[] = $destructCallback;
    }

    /**
     * @param array $classMap Class to filename map
     */
    public function addClassMap(array $classMap)
    {
        if ($this->classMap) {
            $this->classMapModified = true;
            $this->classMap = array_merge($this->classMap, $classMap);
        } else {
            $this->classMap = $classMap;
        }
    }

    /**
     * Registers a set of classes, merging with any others previously set.
     *
     * @param string       $prefix  The classes prefix
     * @param array|string $paths   The location(s) of the classes
     * @param bool         $prepend Prepend the location(s)
     */
    public function add($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirs = array_merge(
                    (array) $paths,
                    $this->fallbackDirs
                );
            } else {
                $this->fallbackDirs = array_merge(
                    $this->fallbackDirs,
                    (array) $paths
                );
            }

            return;
        }
        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = (array) $paths;

            return;
        }
        if ($prepend) {
            $this->prefixes[$prefix] = array_merge(
                (array) $paths,
                $this->prefixes[$prefix]
            );
        } else {
            $this->prefixes[$prefix] = array_merge(
                $this->prefixes[$prefix],
                (array) $paths
            );
        }
    }

    /**
     * Registers a set of classes, replacing any others previously set.
     *
     * @param string       $prefix  The classes prefix
     * @param array|string $paths   The location(s) of the classes
     */
    public function set($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirs = (array) $paths;

            return;
        }
        $this->prefixes[$prefix] = (array) $paths;
    }

    /**
     * Turns on searching the include path for class files.
     *
     * @param bool $useIncludePath
     */
    public function setUseIncludePath($useIncludePath)
    {
        $this->useIncludePath = $useIncludePath;
    }

    /**
     * Can be used to check if the autoloader uses the include path to check
     * for classes.
     *
     * @return bool
     */
    public function getUseIncludePath()
    {
        return $this->useIncludePath;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            include $file;

            return true;
        }
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|false The path if found, false otherwise
     */
    public function findFile($class)
    {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach ($this->prefixes as $prefix => $dirs) {
            if (0 === strpos($class, $prefix)) {
                if (!is_array($dirs)){
                    $dirs = array($dirs);
                }
                foreach ($dirs as $dir) {
                    if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                        $this->classMap[$class] = $dir . DIRECTORY_SEPARATOR . $classPath;
                        $this->classMapModified = true;
                        return $dir . DIRECTORY_SEPARATOR . $classPath;
                    }
                }
            }
        }

        foreach ($this->fallbackDirs as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                $this->classMap[$class] = $dir . DIRECTORY_SEPARATOR . $classPath;
                $this->classMapModified = true;
                return $dir . DIRECTORY_SEPARATOR . $classPath;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
            $this->classMap[$class] = $file;
            $this->classMapModified = true;
            return $file;
        }

        return $this->classMap[$class] = false;
    }

    public function __destruct() {
        foreach ($this->destructCallbacks as $destructCallback){
            call_user_func($destructCallback);
        }
    }
}
