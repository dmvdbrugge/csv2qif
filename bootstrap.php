<?php

use Csv2Qif\RuleSet\Rules\RulesFactory;
use Parable\Console\App;
use Parable\Di\Container;

chdir(__DIR__);
require_once 'vendor/autoload.php';

$di = new Container();

/*
 * Bootstrap the Di Container into
 * all factories here:
 */
RulesFactory::setContainer($di);

$app = $di->get(App::class);
$app->setOnlyUseDefaultCommand(true);

return [$app, $di];
