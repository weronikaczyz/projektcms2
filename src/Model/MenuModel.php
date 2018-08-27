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
class MenuModel
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
     * Downloads every menu entry in database.
     *
     * @return menu entries
     */
    public function getMenuEntries()
    {
        $sql='SELECT * FROM menu';
        return $this->_db->fetchAll($sql);
    }

    /**
     * Downloads every menu entry in database.
     *
     * @param application
     */
    public function loadEntryById($id)
    {
        $sql='SELECT * FROM menu WHERE idmenu = ?';
        return $this->_db->fetchAssoc($sql, array($this->_app->escape($id)));
    }

    /**
     * Downloads left menu entries with status set to ACTIVE.
     *
     * @param application
     */
    public function getLeftMenuEntries()
    {
        $sql='SELECT * FROM menu WHERE position = \'LEFT\' AND active = \'YES\'';
        return $this->_db->fetchAll($sql);
    }

    /**
     * Downloads right menu entries with status set to ACTIVE.
     *
     * @param application
     */
    public function getRightMenuEntries()
    {
        $sql='SELECT * FROM menu WHERE position = \'RIGHT\' AND active = \'YES\'';
        return $this->_db->fetchAll($sql);
    }

    /**
     * Updates menu entry using details from the form.
     *
     * @return bool
     */
    public function saveEntry($data)
    {
        if (isset($data['idmenu']) && ctype_digit((string)$data['idmenu'])) {
            $sql = 'UPDATE menu SET url = ?, description = ?, position = ?, active = ?, external = ?  WHERE idmenu = ?';
            $this->_db->executeQuery(
                $sql, array(
                    $this->_app->escape($data['url']),
                    $this->_app->escape($data['description']),
                    $this->_app->escape($data['position']),
                    $this->_app->escape($data['active']),
                    $this->_app->escape($data['external']),
                    $this->_app->escape($data['idmenu'])
                )
            );
            return 0; // else returning status 1.
        } else {
            return 1;
        }

    }

    /**
     * Creates menu entry using details from the form.
     *
     * @return bool
     */
    public function newEntry($data)
    {
        $sql = 'INSERT INTO menu (`url`, `description`, `position`, `active`, `external`) VALUES (?, ?, ?, ?, ?)';

        $success = $this->_db->executeQuery(
            $sql, array(
                $this->_app->escape($data['url']),
                $this->_app->escape($data['description']),
                $this->_app->escape($data['position']),
                $this->_app->escape($data['active']),
                $this->_app->escape($data['external'])
            )
        );

        if ($success) {
            return 0;
        } else {
            return 1;
        }

    }

    /**
     * Deletes menu entry using given id.
     *
     * @param id
     * @return bool
     */
    public function deleteEntry($id)
    {
        $sql = 'DELETE FROM menu WHERE idmenu = ?';

        $success = $this->_db->executeQuery($sql, array($this->_app->escape($id)));

        if ($success) {
            return 0;
        } else {
            return 1;
        }

    }
}
