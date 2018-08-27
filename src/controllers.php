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

$app->mount('/bookmarks', new BookmarksController());
$app->mount('/auth/', new AuthController());
$app->mount('/account/', new AccountController());