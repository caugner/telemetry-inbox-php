<?php
require_once __DIR__.'/../vendor/autoload.php';

$repo = new Telemetry\Repository(__DIR__ . '/../storage');
$app = new Telemetry\InboxApp($repo);
$app->run();
