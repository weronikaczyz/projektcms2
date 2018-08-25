<?php
/**
 * Init application.
 */
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;

$app = new Application();

$app->register(new AssetServiceProvider());
$app->register(
    new TwigServiceProvider(),
    [
        'twig.path' => dirname(__FILE__).'/templates',
    ]
);

return $app;
