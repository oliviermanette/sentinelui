<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1afc655b346203bc7d2984eb7aa9161b
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '3f8bdd3b35094c73a26f0106e3c0f8b2' => __DIR__ . '/..' . '/sendgrid/sendgrid/lib/SendGrid.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twig\\' => 5,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Polyfill\\Ctype\\' => 23,
            'SendGrid\\Stats\\' => 15,
            'SendGrid\\Mail\\' => 14,
            'SendGrid\\Contacts\\' => 18,
            'SendGrid\\' => 9,
        ),
        'O' => 
        array (
            'OpenCage\\Geocoder\\' => 18,
        ),
        'C' => 
        array (
            'Core\\' => 5,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twig\\' => 
        array (
            0 => __DIR__ . '/..' . '/twig/twig/src',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'SendGrid\\Stats\\' => 
        array (
            0 => __DIR__ . '/..' . '/sendgrid/sendgrid/lib/stats',
        ),
        'SendGrid\\Mail\\' => 
        array (
            0 => __DIR__ . '/..' . '/sendgrid/sendgrid/lib/mail',
        ),
        'SendGrid\\Contacts\\' => 
        array (
            0 => __DIR__ . '/..' . '/sendgrid/sendgrid/lib/contacts',
        ),
        'SendGrid\\' => 
        array (
            0 => __DIR__ . '/..' . '/sendgrid/php-http-client/lib',
            1 => __DIR__ . '/..' . '/sendgrid/sendgrid/lib',
        ),
        'OpenCage\\Geocoder\\' => 
        array (
            0 => __DIR__ . '/..' . '/opencage/geocode/src',
        ),
        'Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Core',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App',
        ),
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Twig_' => 
            array (
                0 => __DIR__ . '/..' . '/twig/twig/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1afc655b346203bc7d2984eb7aa9161b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1afc655b346203bc7d2984eb7aa9161b::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit1afc655b346203bc7d2984eb7aa9161b::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
