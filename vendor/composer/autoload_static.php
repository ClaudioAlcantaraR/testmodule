<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9c856ea9ebd56c0eedd4511b5531c170
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Car\\Customerfield\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Car\\Customerfield\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9c856ea9ebd56c0eedd4511b5531c170::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9c856ea9ebd56c0eedd4511b5531c170::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9c856ea9ebd56c0eedd4511b5531c170::$classMap;

        }, null, ClassLoader::class);
    }
}
