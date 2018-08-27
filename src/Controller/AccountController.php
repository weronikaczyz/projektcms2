<?php
/**
 * This file contains controller of account creation and deletion.
 */

/**
 * Defining namespace and useful components.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;

/**
 * This class contains definitions of account control methods.
 */
class AccountController implements ControllerProviderInterface
{
    protected $_model;

    /**
     * Routing.
     *
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $this->_model = new UsersModel($app);
        $accountController = $app['controllers_factory'];
        $accountController->match(
            '/new', array(
                $this, 'newAccount')
        )
        ->bind(
            '/account/new'
        );
        $accountController->match(
            '/delete', array(
                $this, 'delete')
        )->bind(
            '/account/delete'
        );
        $accountController->match(
            '/admin', array(
                $this, 'admin')
        )
        ->bind(
            '/account/admin'
        );
        $accountController->match(
            '/edit', array(
                $this, 'edit')
        )
        ->bind(
            '/account/edit'
        );
        $accountController->match(
            '/password', array(
                $this, 'editPassword')
        )
        ->bind(
            '/account/password'
        );
        return $accountController;
    }

    /**
     * This method checkes if user is logged in.
     *
     * @param Application $app
     * @return bool
     */
    protected function _isLoggedIn(Application $app)
    {
        if (null === $user = $app['session']->get('user')) {
            return false;
        } else {
            return true; 
        }
    }

    /**
     * @param Application $app
     * @return mixed
     */
    protected function getCurrentUser($app)
    {
        $token = $app['security']->getToken();

        if (null !== $token) {
            $user = $token->getUser()->getUsername();
        }

        return $user;
    }
        
    /**
     * This method defines account creation procedure.
     * 
     * @param application 
     * @param request
     */
    public function newAccount(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'firstname', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(), new Assert\Length(array('min' => 3)))
            )
            )
            ->add(
                'lastname', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(), new Assert\Length(array('min' => 3)))
            )
            )
            ->add(
                'login', 'text', array(
                'constraints' => array(
                    new Assert\NotBlank(), new Assert\Length(array('min' => 3)))
            )
            )
            ->add(
                'password', 'password', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 6)))
            )
            )
            ->add(
                'confirm_password', 'password', array( // confirm password
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 6)))
            )
            )
            ->add(
                'email', 'email', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Email())
            )
            )
                    
            ->add('Enter', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $password = $form['password'] -> getData();
            $passwordConfirm = $form['confirm_password'] -> getData();
            
            if (strcmp($password, $passwordConfirm) == 0) {
                
                $encodedPassword = $app['security.encoder.digest']->encodePassword($password, '');
            
                $user = $this->_model->newAccount($form->getData(), $encodedPassword);
                if (!$user) {
                    $app['session']->getFlashBag()->set('success', 'Account was created!'); 
                    return $app->redirect($app['url_generator']->generate('/auth/login'), 301);
                } else {
                    $app['session']->getFlashBag()->set('error', 'User already exists!');
                    return $app['twig']->render('account/newAccount.twig', array('form' => $form->createView()));
                }
            } else { // passwords do not match
                $app['session']->getFlashBag()->set('error', 'Passwords do not match!');
                return $app['twig']->render('account/newAccount.twig', array('form' => $form->createView()));
            }
        }
        return $app['twig']->render('account/newAccount.twig', array('form' => $form->createView()));
    }
    
    /**
     * This method defines account deletion procedure.
     * 
     * @param application 
     * @param request
     */
    public function delete(Application $app, Request $request)
    {
        $id = $this->getIdCurrentUser($app);

        $user = $this->_model->getUserById($id);

        $data = array();

        if (count($user)) {
            $form = $app['form.factory']->createBuilder('form', $data)
                ->add(
                    'iduser', 'hidden', array(
                    'data' => $id,
                    )
                )
                ->add('Yes', 'submit')
                ->add('No', 'submit')
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($form->get('Yes')->isClicked()) {
                    // user is sure
                    $data = $form->getData();
                    $model = $this->_model->deleteUser($id);
                    if (!$model) {
                        $app['session']->getFlashBag()->set('success', 'Too bad! Account was deleted!');
                        return $app->redirect($app['url_generator']->generate("/"), 301);
                    }
                } else { // this happens when 'No' is clicked
                    $app['session']->getFlashBag()->set('success', 'It is good you have changed your mind!');
                    return $app->redirect($app['url_generator']->generate('/account/edit'), 301);
                    // redirecting to main menu admin site   
                } 
            }
            return $app['twig']->render('account/delete.twig', array('form' => $form->createView()));

        } else { // this happens when application is not able to find the User
            $app['session']->getFlashBag()->set('error', 'User not found!');
            return $app->redirect($app['url_generator']->generate("/"), 301);
        }
    }
    
    /**
     * This method defines account deletion procedure.
     * 
     * @param application 
     * @param request
     */
    public function view(Application $app, Request $request)
    {
        $id = (int) $request->get('id', 0);
        $user = $this->_model->loadUserbyId($id);
        return $app['twig']->render('account/view.twig', array('user' => $user));
    }
    
    /**
     * This method defines how the account may be edited.
     * 
     * @param application 
     * @param request
     */
    public function edit(Application $app, Request $request)
    {
        $id = $this->getIdCurrentUser($app);

        $user = $this->_model->getUserById($id);

        $data = array();

        if (count($user)) {

            $form = $app['form.factory']->createBuilder('form', $user)
                ->add(
                    'idusers', 'hidden', array(
                    'constraints' => array(new Assert\NotBlank())
                )
                )
                ->add(
                    'firstname', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(array('min' => 3)))
                )
                )
                ->add(
                    'lastname', 'text', array(
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(array('min' => 3)))
                )
                )
                ->add(
                    'email', 'email', array(
                    'constraints' => array(new Assert\NotBlank(), new Assert\Email())
                )
                )
                ->getForm();


            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $model = $this->_model->editUser($data);
                if (!$model) {
                    $app['session']->getFlashBag()->set('success', 'Information was updated successfully.');
                    return $app->redirect($app['url_generator']->generate("/"), 301);
                }
            }

            return $app['twig']->render('account/edit.twig', array('form' => $form->createView()));
        } else { // user not logged in
            return $app->redirect($app['url_generator']->generate('/account/new'), 301);
        }
    }
    
    
    /**
     * This method allows users to change their passwords.
     * 
     * @param application 
     * @param request
     */
    public function editPassword(Application $app, Request $request)
    {
        $id = $this->getIdCurrentUser($app);

        $user = $this->_model->getUserById($id);
        
        if (count($user)) {

            $data = array();

            $form = $app['form.factory']->createBuilder('form', $data)
                ->add(
                    'password', 'password', array(
                    'constraints' => array(new Assert\NotBlank(), 
                        new Assert\Length(array('min' => 5)))
                )
                )
                ->add(
                    'new_password', 'password', array(
                    'constraints' => array(new Assert\NotBlank(), 
                    new Assert\Length(array('min' => 5)))
                )
                )
                ->add(
                    'confirm_new_password', 'password', array(
                    'constraints' => array(new Assert\NotBlank(), 
                    new Assert\Length(array('min' => 5)))
                )
                )
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $oldPassword = $app['security.encoder.digest']->encodePassword("{$data['password']}", '');
                
                if ($oldPassword === $user['password']) {

                    if ($data['new_password'] === $data['confirm_new_password']) {

                        $data['new_password'] = $app['security.encoder.digest']
                            ->encodePassword("{$data['new_password']}", '');

                        $model = $this->_model->changePassword($data, $id);

                        if (!$model) {
                            $app['session']->getFlashBag()->set('success', 'Password has been changed!');
                            return $app->redirect($app['url_generator']->generate("/"), 301);
                        }

                    } else { // password do not match
                        $app['session']->getFlashBag()->set('error', 'Passwords do not match!');
                        return $app['twig']->render('account/edit.twig', array('form' => $form->createView()));
                    }
                } else { // password given does not correspond old password
                    $app['session']->getFlashBag()->set('error', 'Password is incorrect!');
                    return $app['twig']->render('account/edit.twig', array('form' => $form->createView()));
                }
            }
        } else { // user not logged in
               return $app->redirect($app['url_generator']->generate('/auth/login'), 301);
        }
      return $app['twig']->render('account/edit.twig', array('form' => $form->createView()));
    }
    
    
    /**
     * This method retrieves current user id.
     * 
     * @param application 
     * @return $userid
     */
    public function getIdCurrentUser($app)
    {
        $login = $this->getCurrentUser($app);
        $iduser = $this->_model->getUserByLogin($login);

        return $iduser['idusers'];
    }
    
    /**
     * This method checks if admin exists. If no - it means we can proceed with page setup.
     * 
     * @param application 
     * @return bool
     */
    public function getAdmin($app)
    {
        $admin = $this->_model->getAdmin($app);
        if ($admin) {
            return 0;
        } else {
            return 1;
        }
    }
    
    
    
}