<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite2766c301bc4760d28e498335235262e
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'SwAlgolia\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'SwAlgolia\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $prefixesPsr0 = array (
        'A' => 
        array (
            'AlgoliaSearch\\Tests' => 
            array (
                0 => __DIR__ . '/..' . '/algolia/algoliasearch-client-php/tests',
            ),
            'AlgoliaSearch' => 
            array (
                0 => __DIR__ . '/..' . '/algolia/algoliasearch-client-php/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite2766c301bc4760d28e498335235262e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite2766c301bc4760d28e498335235262e::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInite2766c301bc4760d28e498335235262e::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
