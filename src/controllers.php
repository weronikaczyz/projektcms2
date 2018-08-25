<?php
/**
 * Routing and controllers.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */
use Controller\BookmarksController;
use Controller\HelloController;

$app->mount('/hello', new HelloController());
$app->mount('/bookmarks', new BookmarksController());
