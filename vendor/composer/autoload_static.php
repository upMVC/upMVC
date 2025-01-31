<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit49502ca6970de9d65a6295b99bbb2d5b
{
    public static $prefixLengthsPsr4 = array (
        'u' => 
        array (
            'upMVC\\' => 6,
        ),
        'U' => 
        array (
            'Userorm\\Routes\\' => 15,
            'Userorm\\' => 8,
            'User\\Routes\\' => 12,
            'User\\' => 5,
        ),
        'T' => 
        array (
            'Test\\Routes\\' => 12,
            'Test\\' => 5,
        ),
        'S' => 
        array (
            'Suba\\Routes\\' => 12,
            'Suba\\' => 5,
        ),
        'R' => 
        array (
            'RedBeanPHP\\' => 11,
            'Reactb\\Routes\\' => 14,
            'Reactb\\' => 7,
            'React\\Routes\\' => 13,
            'React\\Component\\' => 16,
            'React\\' => 6,
            'ReactCrud\\Routes\\' => 17,
            'ReactCrud\\' => 10,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'N' => 
        array (
            'New\\Routes\\' => 11,
            'New\\' => 4,
        ),
        'M' => 
        array (
            'Moda\\Routes\\' => 12,
            'Moda\\' => 5,
            'Mail\\' => 5,
        ),
        'C' => 
        array (
            'Common\\Bmvc\\' => 12,
            'Common\\Assets\\' => 14,
        ),
        'A' => 
        array (
            'Auth\\Routes\\' => 12,
            'Auth\\' => 5,
            'Admin\\Routes\\' => 13,
            'Admin\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'upMVC\\' => 
        array (
            0 => __DIR__ . '/../..' . '/etc',
        ),
        'Userorm\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/userorm/routes',
        ),
        'Userorm\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/userorm',
        ),
        'User\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/user/routes',
        ),
        'User\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/user',
        ),
        'Test\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/test/routes',
        ),
        'Test\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/test',
        ),
        'Suba\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/moda/modules/suba/routes',
        ),
        'Suba\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/moda/modules/suba',
        ),
        'RedBeanPHP\\' => 
        array (
            0 => __DIR__ . '/..' . '/gabordemooij/redbean/RedBeanPHP',
        ),
        'Reactb\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/reactb/routes',
        ),
        'Reactb\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/reactb',
        ),
        'React\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/react/routes',
        ),
        'React\\Component\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/react/etc',
        ),
        'React\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/react',
        ),
        'ReactCrud\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/reactcrud/routes',
        ),
        'ReactCrud\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/reactcrud',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mail/phpmailer',
        ),
        'New\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/new/routes',
        ),
        'New\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/new',
        ),
        'Moda\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/moda/routes',
        ),
        'Moda\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/moda',
        ),
        'Mail\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mail',
        ),
        'Common\\Bmvc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/common/bmvc',
        ),
        'Common\\Assets\\' => 
        array (
            0 => __DIR__ . '/../..' . '/common/assets',
        ),
        'Auth\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/auth/routes',
        ),
        'Auth\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/auth',
        ),
        'Admin\\Routes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/admin/routes',
        ),
        'Admin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/admin',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit49502ca6970de9d65a6295b99bbb2d5b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit49502ca6970de9d65a6295b99bbb2d5b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit49502ca6970de9d65a6295b99bbb2d5b::$classMap;

        }, null, ClassLoader::class);
    }
}
