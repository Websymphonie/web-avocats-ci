<?php


use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

date_default_timezone_set('Africa/Abidjan');
require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool)$context['APP_DEBUG']);
};
