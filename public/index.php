<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

ini_set('memory_limit', '512M');

return function (array $context) {
    date_default_timezone_set( 'Europe/Warsaw' );
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
