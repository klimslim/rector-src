<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\RectorGenerator\Bundle\RectorGeneratorBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/services.php');
    $containerConfigurator->import(__DIR__ . '/services-rules.php');
    $containerConfigurator->import(__DIR__ . '/services-packages.php');
    $containerConfigurator->import(__DIR__ . '/parameters.php');

    // rector root
    $containerConfigurator->import(__DIR__ . '/../vendor/rector/rector-symfony/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../vendor/rector/rector-nette/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../vendor/rector/rector-laravel/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../vendor/rector/rector-phpunit/config/config.php', null, 'not_found');

    // rector sub-package
    $containerConfigurator->import(__DIR__ . '/../../rector-symfony/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../../rector-nette/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../../rector-laravel/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../../rector-phpunit/config/config.php', null, 'not_found');

    // rector root
    $containerConfigurator->import(__DIR__ . '/../vendor/symplify/console-color-diff/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../vendor/symplify/composer-json-manipulator/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../vendor/symplify/skipper/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../vendor/symplify/simple-php-doc-parser/config/config.php', null, 'not_found');

    // rector sub-package
    $containerConfigurator->import(__DIR__ . '/../../../symplify/console-color-diff/config/config.php', null, 'not_found');
    $containerConfigurator->import(__DIR__ . '/../../../symplify/composer-json-manipulator/config/config.php');
    $containerConfigurator->import(__DIR__ . '/../../../symplify/skipper/config/config.php');
    $containerConfigurator->import(__DIR__ . '/../../../symplify/simple-php-doc-parser/config/config.php');

    // only for dev
    if (class_exists(RectorGeneratorBundle::class)) {
        $containerConfigurator->import(__DIR__ . '/../vendor/rector/rector-generator/config/config.php');
    }

    // require only in dev
    $containerConfigurator->import(__DIR__ . '/../utils/compiler/config/config.php', null, 'not_found');

    // to override extension-loaded config
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan-for-rector.neon');
};
