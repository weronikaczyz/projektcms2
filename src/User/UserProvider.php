<?php
/**
 * Created by PhpStorm.
 * User: wercia
 * Date: 26.08.2018
 * Time: 11:39
 */


/**
 * This file contains user provider.
 */


/**
 * Defining namespace and useful components.
 */
namespace User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Model\UsersModel;


/**
 * This class contains definitions of user provider.
 */
class UserProvider implements UserProviderInterface
{

    /**
     * @var _app contains application.
     */
    protected $_app;

    /**
     * Constructor.
     *
     * @param application
     */
    public function __construct($app)
    {
        $this->_app = $app;
    }

    /**
     * Loading user details to session using given login.
     *
     * @param $login
     * @return $user
     */
    public function loadUserByUsername($login)
    {
        $userModel = new UsersModel($this->_app);
        $user = $userModel->loadUserByLogin($login);
        return new User($user['login'], $user['password'], $user['roles'], true, true, true, true);
    }

    /**
     * Refreshing user details to session using given user.
     *
     * @param $user
     * @return $user
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Sypporting class.
     *
     * @param $class
     * @return $class
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}