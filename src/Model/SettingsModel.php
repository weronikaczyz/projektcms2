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
class SettingsModel
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
     * @param $id
     * @return int
     */

    public function updateSettings($id)
    {
        if (isset($id) && ctype_digit((string)$id)) {
            $sql = 'UPDATE settings SET att_value = ? WHERE att_name = "homepage" VALUES ()';
            $success = $this->_db->executeQuery ($sql, array ($this->_app->escape($id)));
            //var_dump($id);
            if ($success) {
                return 0;
            } else {
                return 1;
            }
        }

    }

    /**
     * Gets setting from database depending on option given.
     *
     * @return option
     */
    public function getSetting($option)
    {
        $sql = 'SELECT att_value FROM settings WHERE att_name = ? LIMIT 1';
        $setting = $this->_db->fetchAssoc($sql, array($this->_app->escape($option)));
        return $setting['att_value'];
    }

}