<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit422664bc61183934c7444288e0c2e430
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            include __DIR__ . '/ClassLoader.php';
        }
    }

    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit422664bc61183934c7444288e0c2e430', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader();
        spl_autoload_unregister(array('ComposerAutoloaderInit422664bc61183934c7444288e0c2e430', 'loadClassLoader'));

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            include_once __DIR__ . '/autoload_static.php';

            call_user_func(\Composer\Autoload\ComposerStaticInit422664bc61183934c7444288e0c2e430::getInitializer($loader));
        } else {
            $map = include __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) {
                $loader->set($namespace, $path);
            }

            $map = include __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) {
                $loader->setPsr4($namespace, $path);
            }

            $classMap = include __DIR__ . '/autoload_classmap.php';
            if ($classMap) {
                $loader->addClassMap($classMap);
            }
        }

        $loader->register(true);

        if ($useStaticLoader) {
            $includeFiles = Composer\Autoload\ComposerStaticInit422664bc61183934c7444288e0c2e430::$files;
        } else {
            $includeFiles = include __DIR__ . '/autoload_files.php';
        }
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequire422664bc61183934c7444288e0c2e430($fileIdentifier, $file);
        }

        return $loader;
    }
}

function composerRequire422664bc61183934c7444288e0c2e430($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        include $file;

        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
    }
}
