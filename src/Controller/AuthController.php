<?php
/**
 * This file contains controller of user authorization.
 */


/**
 * Defining namespace and useful components.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;

/**
 * This class contains definitions of user authorization methods.
 */
class AuthController implements ControllerProviderInterface
{
    /**
     * Routing
     *
     * @param Application $app
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $authController = $app['controllers_factory'];
        $authController->match('/login', array($this, 'login'))->bind('/auth/login');
        $authController->match('/logout', array($this, 'logout'))->bind('/auth/logout');
        return $authController;
    }

    /**
     * This method defines login procedure.
     *
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function login(Application $app, Request $request)
    {
        $form = $app['form.factory']->createBuilder(FormType::class)
            ->add(
                'username', TextType::class, array(
                    'label' => 'Username',
                    'data' => $app['session']->get('_security.last_username')
                )
            )
            ->add('password', PasswordType::class, array('label' => 'Password'))
            ->add('submit', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        return $app['twig']->render(
            'auth/login.twig', array(
                'form' => $form->createView(),
                'error' => $app['security.last_error']($request)
            )
        );
    }

    /**
     * This method defines logout procedure.
     *
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
    public function logout(Application $app, Request $request)
    {
        $app['session']->clear();
        return $app['twig']->render('auth/logout.twig');
    }

}