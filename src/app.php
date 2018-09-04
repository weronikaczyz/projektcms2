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
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

$app = new Application();

//$app->match('/admin/check', function () {});

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
            'host'      => '127.0.0.1',
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
$app['security.encoder.bcrypt.cost'] = 13;
$app['security.encoder.bcrypt'] = function ($app) {
    return new BCryptPasswordEncoder($app['security.encoder.bcrypt.cost']);
};
$app['security.default_encoder'] = function ($app) {
//    return $app['security.encoder.bcrypt'];
    return new PlaintextPasswordEncoder();
};

$app['homepage'] = 1; // TODO: pobrac z bazy danych ID homepage $settingsModel->getSetting('background');

//$app->register(
//    new SecurityServiceProvider(), array(
//        'security.firewalls' => array(
//            'secured' => array(
//                'pattern' => '^/admin/',
//                'form' => array(
//                    'login_path' => '/auth/login',
//                    'check_path' => '/admin/check',
//                    'username_parameter' => 'form[username]',
//                    'password_parameter' => 'form[password]',
//                ),
//                'logout' => array('logout_path' => '/auth/logout'),
//                'users' => function () use ($app) {
//                    return new UserProvider($app);
//                },
//            )
//        )
//    )
//);

//$app->register(
//    new SecurityServiceProvider(), array(
//        'security.firewalls' => array(
//            'admin' => array(
////                'pattern' => '^.*$',
//                'pattern' => '^/admin',
//                'http' => true,
//                'form' => array(
//                    'login_path' => '/auth/login',
//                    'check_path' => '/admin/login_check',
//                    'default_target_path'=> '/',
//                    'username_parameter' => 'form[username]',
//                    'password_parameter' => 'form[password]',
//                ),
////                'logout'  => true,
//                'logout' => array('logout_path' => '/auth/logout'),
//                'users' => function () use ($app) {
//                    return new UserProvider($app);
//                },
//            ),
//            'unsecured' => array(
//                'anonymous' => true
//            ),
//        ),
//
//        'security.access_rules' => array(
//            array(
//                '^/$|^/auth/.+$|^/pages/display|^/account/new.*$|^/comments/.*$|^/pages/.*$',
//                'IS_AUTHENTICATED_ANONYMOUSLY'),
//            array(
//                '^/$|^/pages/display|^/account/edit.*$|^/account/delete.*$',
//                'ROLE_EDITOR'
//            ),
////            array('^/.+$', 'ROLE_ADMIN')
//        ),
//        'security.role_hierarchy' => array(
//            'ROLE_ADMIN' => array('ROLE_EDITOR', 'ROLE_ANONYMOUS'),
//            'ROLE_EDITOR' => array('ROLE_ANONYMOUS'),
//        ),
//    )
//);


return $app;
