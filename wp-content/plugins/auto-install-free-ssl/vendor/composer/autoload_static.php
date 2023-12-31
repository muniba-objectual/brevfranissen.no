<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit38dd793580206d82dbe1f232ba20bd21
{
    public static $files = array (
        '7c9b72b4e40cc7adcca6fd17b1bf4c8d' => __DIR__ . '/..' . '/indigophp/hash-compat/src/hash_equals.php',
        '43d9263e52ab88b5668a28ee36bd4e65' => __DIR__ . '/..' . '/indigophp/hash-compat/src/hash_pbkdf2.php',
    );

    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'AutoInstallFreeSSL\\FreeSSLAuto\\' => 31,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'AutoInstallFreeSSL\\FreeSSLAuto\\' => 
        array (
            0 => __DIR__ . '/../..' . '/FreeSSLAuto/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit38dd793580206d82dbe1f232ba20bd21::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit38dd793580206d82dbe1f232ba20bd21::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit38dd793580206d82dbe1f232ba20bd21::$classMap;

        }, null, ClassLoader::class);
    }
}
