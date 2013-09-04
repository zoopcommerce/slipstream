<?php

class SlipstreamAutoloaderInit
{
    private static $loader;

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        include __DIR__ . '/ClassLoader.php';
        self::$loader = $loader = new Zoop\Slipstream\ClassLoader;

        $vendorDir = dirname(dirname(__DIR__));

        $map = require $vendorDir . '/composer/autoload_namespaces.php';
        foreach ($map as $namespace => $path) {
            $loader->set($namespace, $path);
        }

        $classMap = require $vendorDir . '/composer/autoload_classmap.php';
        if ($classMap) {
            $loader->addClassMap($classMap);
        }

        $loader->register(true);
        $loader->addDestructCallback('SlipstreamAutoloaderInit::persistModifiedClassmap');

        return $loader;
    }

    public static function persistModifiedClassmap(){

        $loader = self::getLoader();
        if ( ! $loader->getClassMapModified()){
            return false;
        }

        $code = "<?php\n// Re-generated by zoop slipstream\nreturn ";
        $code .= var_export($loader->getClassMap(), true) . ";\n";
        file_put_contents(dirname(dirname(__DIR__)) . '/composer/autoload_classmap.php', $code);

        return true;
    }
}

return SlipstreamAutoloaderInit::getLoader();
