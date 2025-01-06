<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitff1056f71c6dba41d243124bfd07b96d
{
    public static $files = array (
        'bb2ad5156ac7a5ea5170e3032f9986c5' => __DIR__ . '/../..' . '/modules/meta-box/meta-box.php',
        '9195b8cf8e415449278c0c4efa05f57e' => __DIR__ . '/../..' . '/modules/mb-acf-migration/mb-acf-migration.php',
        'd45d9ba5e80690d08cb8d4c4237b57c3' => __DIR__ . '/../..' . '/modules/mb-comment-meta/mb-comment-meta.php',
        'f4f8943a33e9331987b83c5dbca3cbb8' => __DIR__ . '/../..' . '/modules/mb-custom-post-type/mb-custom-post-type.php',
        '12bf048f557fcdf96bd657582056dc13' => __DIR__ . '/../..' . '/modules/mb-divi-integrator/mb-divi-integrator.php',
        '53e5f459e3b3bd3d363b57f3762d653e' => __DIR__ . '/../..' . '/modules/mb-elementor-integrator/mb-elementor-integrator.php',
        'c031731594be3c134da95963db29771b' => __DIR__ . '/../..' . '/modules/mb-rank-math/mb-rank-math.php',
        'fa0768d6b1dd479a572914a99c2af7a3' => __DIR__ . '/../..' . '/modules/mb-relationships/mb-relationships.php',
        '2bea1f0614b56f1133800caeae5e6df5' => __DIR__ . '/../..' . '/modules/mb-rest-api/mb-rest-api.php',
        'a2e46144531a082e88682548cbcecd51' => __DIR__ . '/../..' . '/modules/mb-toolset-migration/mb-toolset-migration.php',
        'fe708c586b6dc4f5b534e5cd329cf512' => __DIR__ . '/../..' . '/modules/mb-yoast-seo/mb-yoast-seo.php',
        '30a7ba7060fe32310e7146ac1c287328' => __DIR__ . '/../..' . '/modules/meta-box-beaver-themer-integrator/meta-box-beaver-themer-integrator.php',
        '8df026c8c79982bce14306d3e3e1c9cc' => __DIR__ . '/../..' . '/modules/meta-box-builder/meta-box-builder.php',
        '0f9e0ce15017230bccbcc2bb4d0c0ef6' => __DIR__ . '/../..' . '/modules/meta-box-facetwp-integrator/meta-box-facetwp-integrator.php',
        '8c74dad9fda44bae6e5e167d1597598d' => __DIR__ . '/../..' . '/modules/text-limiter/text-limiter.php',
    );

    public static $prefixLengthsPsr4 = array (
        'e' => 
        array (
            'eLightUp\\' => 9,
        ),
        'R' => 
        array (
            'Riimu\\Kit\\PHPEncoder\\' => 21,
        ),
        'M' => 
        array (
            'MetaBox\\TS\\' => 11,
            'MetaBox\\Support\\' => 16,
            'MetaBox\\RestApi\\' => 16,
            'MetaBox\\ACF\\' => 12,
            'MetaBox\\' => 8,
            'MBEI\\' => 5,
            'MBDI\\' => 5,
            'MBCPT\\' => 6,
            'MBB\\SettingsPage\\' => 17,
            'MBB\\Relationships\\' => 18,
            'MBB\\' => 4,
            'MBBTI\\' => 6,
            'MBBParser\\' => 10,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'eLightUp\\' => 
        array (
            0 => __DIR__ . '/..' . '/elightup/twig',
        ),
        'Riimu\\Kit\\PHPEncoder\\' => 
        array (
            0 => __DIR__ . '/..' . '/riimu/kit-phpencoder/src',
        ),
        'MetaBox\\TS\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mb-toolset-migration/src',
        ),
        'MetaBox\\Support\\' => 
        array (
            0 => __DIR__ . '/..' . '/meta-box/support',
        ),
        'MetaBox\\RestApi\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mb-rest-api/src',
        ),
        'MetaBox\\ACF\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mb-acf-migration/src',
        ),
        'MetaBox\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/meta-box/src',
        ),
        'MBEI\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mb-elementor-integrator/src',
        ),
        'MBDI\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mb-divi-integrator/src',
        ),
        'MBCPT\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/mb-custom-post-type/src',
        ),
        'MBB\\SettingsPage\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/meta-box-builder/modules/settings-page/src',
        ),
        'MBB\\Relationships\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/meta-box-builder/modules/relationships/src',
        ),
        'MBB\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/meta-box-builder/src',
        ),
        'MBBTI\\' => 
        array (
            0 => __DIR__ . '/../..' . '/modules/meta-box-beaver-themer-integrator/src',
        ),
        'MBBParser\\' => 
        array (
            0 => __DIR__ . '/..' . '/meta-box/mbb-parser/src',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitff1056f71c6dba41d243124bfd07b96d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitff1056f71c6dba41d243124bfd07b96d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitff1056f71c6dba41d243124bfd07b96d::$classMap;

        }, null, ClassLoader::class);
    }
}
