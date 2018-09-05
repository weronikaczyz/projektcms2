<?php
/**
 * This file contains model of menu administration
 */


/**
 * Defining namespace and useful components.
 */
namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * This class contains definitions of menu administration database requests.
 */
class PhotosModel
{
    protected $_db;
    protected $_app;
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
        $this->_app = $app;
    }


    /**
     * This function does the actual database insertion.
     *
     * @param $name
     * @return void
     */
    public function saveFile($name)
    {
        $sql = 'INSERT INTO `photos` (`photo_name`) VALUES (?)';
        $this->_db->executeQuery($sql, array($this->_app->escape($name)));
    }


    /**
     * This function creates unique name.
     * @param $name
     * @return $newName
     */
    public function createName($name)
    {
        $newName = '';
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = $this->_randomString(32) . '.' . $ext;

        while (!$this->_isUniqueName($newName)) {
            $newName = $this->_randomString(32) . '.' . $ext;
        }

        return $newName;
    }


    /**
     * This function creates unique string to be connected with name later on.
     * @param $length
     * @return $string
     */
    protected function _randomString($length)
    {
        $string = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < $length; $i++) {
            $string .= $keys[array_rand($keys)];
        }
        return $string;
    }

    /**
     * This function checkes if the name is unique.
     * @param $value
     * @return $bool
     */
    protected function _isUniqueName($value)
    {
        $sql = 'SELECT COUNT(*) AS files_count FROM photos WHERE photo_name = ?';
        $result = $this->_db->fetchAssoc($sql, array($this->_app->escape($value)));
        return !$result['files_count'];
    }

    /**
     * This function selects all photos from database.
     *
     *
     * @return void
     */
    public function gallery()
    {
        $sql = 'SELECT * FROM `photos`';
        return $this->_db->fetchAll($sql);
    }


    /**
     * This function retrieves id of new photo.
     *
     *
     * @return void
     */
    public function getId()
    {
        $identity = $this->_db->lastInsertId();
        return $identity;

    }


}