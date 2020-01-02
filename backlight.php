#!/usr/bin/env php
<?php

use Aperture\Backlight\Command\GetCommand;
use Aperture\Backlight\Command\SetCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application('backlight', '0.1.1');

$app->addCommands(
    [
        new GetCommand(),
        new SetCommand(),
    ]
);

/** @noinspection PhpUnhandledExceptionInspection */
$app->run();
