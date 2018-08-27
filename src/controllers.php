<?php
/**
 * Routing and controllers.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */
use Controller\BookmarksController;
use Controller\AuthController;
use Controller\AccountController;
use Controller\PagesController;
use Controller\AdminController;

$app->mount('/', new PagesController()); // root URL
$app->mount('/bookmarks', new BookmarksController());
$app->mount('/auth/', new AuthController());
$app->mount('/account/', new AccountController());
$app->mount('/admin/', new AdminController());
