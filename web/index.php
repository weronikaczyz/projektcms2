
/**
 * Application front controller for `production` environment.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', false);

//require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

//$app = require_once dirname(dirname(__FILE__)).'/src/app.php';

//require_once dirname(dirname(__FILE__)).'/src/controllers.php';

//$app->run();

<?php
/**
 * This is the main project file. It defines all controllers, service providers
 * and runs the application.
 *
 */

/**
 * Initializing silex application.
 */
require_once __DIR__.'/../vendor/autoload.php';
$app = new Silex\Application();
$app['debug'] = false;

/**
 * Registering all ServiceProviders.
 */
$app->register(
    new Silex\Provider\TwigServiceProvider(),
    array('twig.path' => __DIR__.'/../src/views')
);

$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(
    new Silex\Provider\TranslationServiceProvider(),
    array('translator.domains' => array())
);
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

/**
 * Global variables
 *
 *
 */
$app['option'] = NULL;
$app['setup'] = NULL;
$app['logo'] = NULL;
$app['background'] = NULL;
$app['theme'] = NULL;
$app['layout'] = NULL;
$app['title'] = 'NULL';
$app['footer'] = 'NULL';


/**
 * Connecting to database.
 */
$app->register(
    new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'projekt',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8',
        ))
);




/**
 * Mounting all Controllers.
 */
$app->mount('/auth/', new Controller\AuthController());
$app->mount('/account/', new Controller\AccountController());
$app->mount('/menu/', new Controller\MenuController());
$app->mount('/pages/', new Controller\PagesController());
$app->mount('/global/', new Controller\GlobalController());
$app->mount('/photos/', new Controller\PhotosController());

$pagesController = new Controller\PagesController($app);

$app->get(
    '/', function() use ($app) {
    return $app->redirect(
        $app["url_generator"]->generate(
            "/pages/display",
            array('id'=>'1')
        )
    );
}
)->bind('/');

$menuController = new Controller\MenuController($app);
$app['leftMenu'] = $menuController -> leftMenu($app);
$app['rightMenu'] = $menuController -> rightMenu($app);

$globalController = new Controller\GlobalController($app);
$isSetup = $globalController -> isSetup($app);
if ($isSetup == 'true') {
    $app['setup'] = true;
} else {
    $app['setup'] = false;
}
//echo $app['setup'];
$app['logo'] = $globalController -> getCurrentLogo($app);
$app['background'] = $globalController -> getCurrentBackground($app);
$app['theme'] = $globalController -> getTheme($app);
$app['layout'] = $globalController -> getLayout($app);
$app['title'] = $globalController -> getCurrentTitle($app);
$app['footer'] = $globalController -> getCurrentFooter($app);



if ($app['setup'] === true) {
    $app->register(
        new Silex\Provider\SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'admin' => array(
                    'pattern' => '^.*$',
                    'form' => array(
                        'login_path' => '/auth/login',
                        'check_path' => '/albums/login_check',
                        'default_target_path'=> '/global/title',
                        'username_parameter' => 'form[username]',
                        'password_parameter' => 'form[password]',
                    ),
                    'logout'  => true,
                    'anonymous' => true,
                    'logout' => array('logout_path' => '/auth/logout'),
                    'users' => $app->share(
                        function() use ($app) {
                            return new User\UserProvider($app);
                        }
                    ),
                ),
            ),
            'security.access_rules' => array(
                array(
                    '^/auth/.+$|^/pages/display|^/account/new.*$|^/comments/.*$|^/pages/.*$',
                    'IS_AUTHENTICATED_ANONYMOUSLY'),
                array('^/pages/display|^/account/edit.*$|^/account/delete.*$', 'ROLE_USER'),
                array('^/.+$', 'ROLE_ADMIN')
            ),
            'security.role_hierarchy' => array(
                'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ANONYMUS'),
                'ROLE_USER' => array('ROLE_ANONYMUS'),
            ),
        )
    );
} else {
    $app->register(
        new Silex\Provider\SecurityServiceProvider(), array(
            'security.firewalls' => array(
                'admin' => array(
                    'pattern' => '^.*$',
                    'form' => array(
                        'login_path' => '/auth/login',
                        'check_path' => '/albums/login_check',
                        'default_target_path'=> '/pages/display/4',
                        'username_parameter' => 'form[username]',
                        'password_parameter' => 'form[password]',
                    ),
                    'logout'  => true,
                    'anonymous' => true,
                    'logout' => array('logout_path' => '/auth/logout'),
                    'users' => $app->share(
                        function() use ($app) {
                            return new User\UserProvider($app);
                        }
                    ),
                ),
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
}



/**
 * Running the application.
 */
$app->run();