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
     * Downloads current logo path from database.
     *
     * @return logo path
     */
    public function getCurrentLogo()
    {
        $id = $this -> getLogoId();
        $sql = 'SELECT photo_name FROM photos WHERE idphotos = ?';
        $path = $this->_db->fetchAssoc($sql, array($this->_app->escape($id['att_value'])));
        //var_dump($path);
        return $path['photo_name'];
    }

    /**
     * Downloads current background path from database.
     *
     * @return background path
     */
    public function getCurrentBackground()
    {
        $id = $this -> getBackgroundId();
        $sql = 'SELECT photo_name FROM photos WHERE idphotos = ?';
        $path = $this->_db->fetchAssoc($sql, array($this->_app->escape($id['att_value'])));
        //var_dump($path);
        return $path['photo_name'];
    }


    /**
     * Gets id of logo picture from database.
     *
     * @return logo id
     */
    public function getLogoId()
    {
        $sql = 'SELECT att_value FROM settings WHERE att_name = \'LOGO\' LIMIT 1';
        $id = $this->_db->fetchAssoc($sql);
        //var_dump($id);
        return $id;
    }

    /**
     * Gets id of background picture from database.
     *
     * @return background id
     */
    public function getBackgroundId()
    {
        $sql = 'SELECT att_value FROM settings WHERE att_name = \'BACKGROUND\' LIMIT 1';
        $id = $this->_db->fetchAssoc($sql);
        //var_dump($id);
        return $id;
    }

    /**
     * Replaces current logo with the new one.
     *
     * @return bool
     */
    public function updateSettings($id, $option)
    {
        if (isset($id) && ctype_digit((string)$id)) {
            $sql = 'UPDATE settings SET att_value = ? WHERE att_name = ?';
            $success = $this->_db->executeQuery($sql, array($this->_app->escape($id), $this->_app->escape($option)));
            //var_dump($id);
            if ($success) {
                return 0;
            } else {
                return 1;
            }
        }


    }

    /**
     * Deletes logo entry.
     *
     * @return bool
     */
    public function deleteLogo()
    {
        $sql = 'UPDATE settings SET att_value = \'NULL\' WHERE att_name = \'LOGO\'';
        $success = $this->_db->executeQuery($sql);
        //var_dump($id);
        if ($success) {
            return 0;
        } else {
            return 1;
        }

    }

    /**
     * Deletes logo entry.
     *
     * @return bool
     */
    public function deleteBackground()
    {
        $sql = 'UPDATE settings SET att_value = \'NULL\' WHERE att_name = \'BACKGROUND\'';
        $success = $this->_db->executeQuery($sql);
        //var_dump($id);
        if ($success) {
            return 0;
        } else {
            return 1;
        }

    }


    /**
     * Replaces current theme with the new one.
     *
     * @return bool
     */
    public function editTheme($data)
    {
        $sql = 'UPDATE settings SET att_value = ? WHERE att_name = \'THEME\'';
        $success = $this->_db->executeQuery($sql, array($this->_app->escape($data)));
        //var_dump($id);
        if ($success) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Replaces current title with the new one.
     *
     * @return bool
     */
    public function editTitle($data)
    {
        $sql = 'UPDATE settings SET att_value = ? WHERE att_name = \'TITLE\'';
        $success = $this->_db->executeQuery($sql, array($this->_app->escape($data)));
        //var_dump($id);
        if ($success) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Replaces current footer with the new one.
     *
     * @return bool
     */
    public function editFooter($data)
    {
        $sql = 'UPDATE settings SET att_value = ? WHERE att_name = \'FOOTER\'';
        $success = $this->_db->executeQuery($sql, array($this->_app->escape($data)));
        //var_dump($id);
        if ($success) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Sets SETUP value to false.
     *
     * @return bool
     */
    public function stopSetup($data)
    {
        $sql = 'UPDATE settings SET att_value = \'false\' WHERE att_name = \'SETUP\'';
        $success = $this->_db->executeQuery($sql);
        //var_dump($id);
        if ($success) {
            return 0;
        } else {
            return 1;
        }
    }


    /**
     * Replaces current layout with the new one.
     *
     * @return bool
     */
    public function editLayout($data)
    {
        $sql = 'UPDATE settings SET att_value = ? WHERE att_name = \'LAYOUT\'';
        $success = $this->_db->executeQuery($sql, array($this->_app->escape($data)));
        if ($success) {
            return 0;
        } else {
            return 1;
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