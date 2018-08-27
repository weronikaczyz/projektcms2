<?php
/**
 * Init application.
 */
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use User\UserProvider;

$app = new Application();

$app->register(new AssetServiceProvider());
$app->register(
    new TwigServiceProvider(),
    [
        'twig.path' => dirname(__FILE__).'/templates',
    ]
);
$app->register(
    new DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'projekt',
            'user'      => 'root',
            'password'  => 'rootroot',
            'charset'   => 'utf8',
        ))
);
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new LocaleServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'translator.domains' => array(),
));
$app->register(
    new SecurityServiceProvider(), array(
        'security.firewalls' => array(
            'admin' => array(
//                'pattern' => '^.*$',
                'pattern' => '^/admin',
                'http' => true,
                'form' => array(
                    'login_path' => '/auth/login',
                    'check_path' => '/account/admin',
                    'default_target_path'=> '/',
                    'username_parameter' => 'form[username]',
                    'password_parameter' => 'form[password]',
                ),
//                'logout'  => true,
                'anonymous' => true,
                'logout' => array('logout_path' => '/auth/logout'),
                'users' => function () use ($app) {
                    return new UserProvider($app['db']);
                },
            ),
        ),
        'unsecured' => array(
            'anonymous' => true
        ),
        'security.access_rules' => array(
            array(
                '^/auth/.+$|^/pages/display|^/account/new.*$|^/comments/.*$|^/pages/.*$',
                'IS_AUTHENTICATED_ANONYMOUSLY'),
            array(
                '^/pages/display|^/account/edit.*$|^/account/delete.*$',
                'ROLE_USER'
            ),
            array('^/.+$', 'ROLE_ADMIN')
        ),
        'security.role_hierarchy' => array(
            'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ANONYMUS'),
            'ROLE_USER' => array('ROLE_ANONYMUS'),
        ),
    )
);


return $app;
