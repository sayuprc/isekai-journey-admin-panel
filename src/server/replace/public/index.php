<?php

declare(strict_types=1);

use Support\Path\Path;
use Tempest\Router\HttpApplication;

require_once __DIR__ . '/../vendor/autoload.php';

Path::setRoot(__DIR__ . '/../');

HttpApplication::boot(__DIR__ . '/../')->run();
