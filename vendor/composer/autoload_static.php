<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit81497d6b42af72aaab536f3a8a2d22a0
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Ear2Words\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ear2Words\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Ear2Words\\Api\\ApiRequest' => __DIR__ . '/../..' . '/includes/Api/ApiRequest.php',
        'Ear2Words\\Gutenberg\\VideoBlock' => __DIR__ . '/../..' . '/includes/Gutenberg/VideoBlock.php',
        'Ear2Words\\Loader' => __DIR__ . '/../..' . '/includes/Loader.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit81497d6b42af72aaab536f3a8a2d22a0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit81497d6b42af72aaab536f3a8a2d22a0::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit81497d6b42af72aaab536f3a8a2d22a0::$classMap;

        }, null, ClassLoader::class);
    }
}
