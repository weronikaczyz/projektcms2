<?php

/**
 * Defining namespace and useful components.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Model\SettingsModel;

/**
 *  This class defines global settings if the website including theme, layout, logo, footer, header etc.
 *
 * @package Controller
 */
class SettingsController implements ControllerProviderInterface
{
    /**
     * @var \Model\SettingsModel $_model
     */
    protected $_model;

    /**
     * Routing.
     *
     * @param Application $app
     * @return mixed $settingsController
     */
    public function connect(Application $app)
    {
        $this->_model = new SettingsModel($app);
        $settingsController = $app['controllers_factory'];
//        $settingsController->match('/admin', array($this, 'index'))->bind('/settings/admin');
        $settingsController->match('settings/homepage', array($this, 'setAsHomepage'))->bind('/settings/homepage');

        return $settingsController;
    }

    /**
     * This method allows admin to maintain the page.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index(Application $app, Request $request)
    {
        if ($app['security']->isGranted('ROLE_ADMIN')) {
            return $app['twig']->render('settings/index.twig');
        } else {
            return $app->redirect($app['url_generator']->generate('/auth/login'), 301);
        }
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function setAsHomepage(Application $app, Request $request)
    {
        if ($id = (int)$request->get('id')) {
            return $this->_model->updateSetting($id, 'homepage');
        } else {
            return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);
        }

        
        //  $id = (int)$request->get('id');
        // TODO: sprawdzic czy strona z $id istnieje
        // TODO: zapisac w bazie danych $id homepage
        // $this->_model->updateSetting($id, 'homepage');
        //return $app->redirect($app['url_generator']->generate('/pages/admin'), 301);

    }
}
