<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../controllers/index.php';
require_once __DIR__ . '/../controllers/data.php';

$app->run();
