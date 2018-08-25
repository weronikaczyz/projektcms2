<?php

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UsersModel
{

    /**
     * @var _app contains application.
     */
    protected $_app;
    
    /**
     * @var _db contains database.
     */
    protected $_db;
    
    /**
     * Constructor.
     * 
     * @param application 
     */
    public function __construct(Application $app)
    {
        $this->_app = $app;
        $this->_db = $app['db'];
    }
    
    
    /**
     * Loading user details to session using given login.
     * 
     * @param $login
     * @return $user
     */
    public function loadUserByLogin($login)
    {
        $data = $this->getUserByLogin($login);

        if (!$data) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $login));
        }

        $roles = $this->getUserRoles($data['idusers']);

        if (!$roles) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $login));
        }

        $user = array(
            'login' => $data['login'],
            'password' => $data['password'],
            'roles' => $roles
        );

        return $user;
    }

    /**
     * Connecting to database and getting user details using given login.
     * 
     * @param $login
     * @return array
     */
    public function getUserByLogin($login)
    {
        $sql = 'SELECT * FROM users WHERE login = ?';
        return $this->_db->fetchAssoc($sql, array((string) $this->_app->escape($login)));
    }
    
    /**
     * Connecting to database and getting user details using given id.
     * 
     * @param $id
     * @return array
     */
    public function getUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE `idusers` = ? Limit 1';
        return $this->_db->fetchAssoc($sql, array((int)$this->_app->escape($id)));
    }
    
    /**
     * Connecting to database and getting user details using given id.
     * 
     * @param $id
     * @return array
     */
    public function getAllAccounts()
    {
        $sql = 'SELECT * FROM users';
        return $this->_db->fetchAll($sql);
    }
    
    /**
     * Loading user details to session using given id.
     * 
     * @param $id
     * @return $roles
     */
    public function getUserRoles($userId)
    {
        $sql = '
            SELECT
                roles.role
            FROM
                users_roles
            INNER JOIN
                roles
            ON users_roles.role_id=roles.id
            WHERE
                users_roles.user_id = ?
            ';

        $result = $this->_db->fetchAll($sql, array((string) $this->_app->escape($userId)));

        $roles = array();
        foreach ($result as $row) {
            $roles[] = $row['role'];
        }

        return $roles;
    }
    
    /**
     * This method allows people to create new accounts.
     * 
     * @param $data
     * @return bool
     */
    public function newAccount($data, $encodedPassword)
    {
        $check = NULL;
        $check = $this->getUserByLogin($data['login']);
            
        if (!$check) {
            $sql = 'INSERT INTO users (firstname, lastname, login, password, email) VALUES (?,?,?,?,?); ';
            $this->_db->executeQuery(
                $sql, array(
                    $this->_app->escape($data['firstname']), 
                    $this->_app->escape($data['lastname']), 
                    $this->_app->escape($data['login']), 
                    $this->_app->escape($encodedPassword),  
                    $this->_app->escape($data['email'])
                )
            );
                
            $sql2 = "SELECT * FROM users WHERE login =\"".$this->_app->escape($data['login'])."\";";
            $user = $this->_db->fetchAssoc($sql2);
            $sql3 = 'INSERT INTO users_roles (`user_id`, `role_id` ) VALUES(?, ?)';
            $this->_db->executeQuery($sql3, array($user['idusers'], 2));
            return 0;
        } else {
            return 1;   
        }   
    }
    
    /**
     * This method allows people to edit their accounts.
     * 
     * @param $data
     * @return bool
     */
    public function editUser($data)
    {
        if (isset($data['idusers']) && ctype_digit((string)$data['idusers'])) {
            $sql = 'UPDATE users SET firstname = ?, lastname = ?, email = ? WHERE idusers = ?';
            $success = $this->_db->executeQuery(
                $sql, array(
                    $this->_app->escape($data['firstname']),
                    $this->_app->escape($data['lastname']), 
                    $this->_app->escape($data['email']), 
                    $this->_app->escape($data['idusers'])
                )
            );
            if ($success) {
                return 0;
            } else {
                return 1;
            }   
        } else {
            return 1;
        }
    }
    
    /**
     * This method allows people to edit their accounts.
     * 
     * @param $data
     * @return bool
     */
    public function deleteUser($id)
    {
        if (isset($id) && ctype_digit((string)$id)) {
            $sql = 'DELETE FROM users_roles WHERE user_id = ?';
            $success = $this->_db->executeQuery($sql, array($this->_app->escape($id)));     
            
            if ($success) {
                $sql2 = 'DELETE FROM users WHERE idusers = ?';
                $success2 = $this->_db->executeQuery($sql2, array($this->_app->escape($id)));
                    
                if ($success2) {
                    return 0;
                } else {
                    return 1;
                }
            } else {
                return 1;
            }           
        }
    }
    
    /**
     * This method allows people to change their passwords.
     * 
     * @param $data
     * @return bool
     */
    public function changePassword($data, $id)
    {
        $sql = "UPDATE `users` SET `password`=? WHERE `idusers`= ?";

        $success = $this->_db->executeQuery(
            $sql, array(
                $this->_app->escape(
                    $data['new_password']
                ), 
                $this->_app->escape($id)
            )
        );
        if ($success) {
            return 0;
        } else {
            return 1;
        }
    }

}