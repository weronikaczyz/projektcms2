<?php
/**
 * This file contains model of pages administration.
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
 * This class contains definitions of pages administration database requests.
 */
class PagesModel
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
     * @return array
     */
    public function getPagesEntries()
    {
        $sql='SELECT idpages, title, published FROM pages';
        return $this->_db->fetchAll($sql);
    }

    /**
     * Downloads every menu entry in database.
     *
     * @param id
     * @return page entry array
     */
    public function getPage($id)
    {
        if (isset($id) && ctype_digit((string)$id)) {
            $sql='SELECT * FROM pages WHERE idpages = ?';
            return $this->_db->fetchAssoc($sql, array($this->_app->escape($id)));
//            $page['published'] = true;
//            $page['title'] = 'tytuł';
//            $page['content'] = 'testd dgdgdsgs';
//            return $page;
        } else {
            return 1;
        }
    }

    public function saveEntry($data)
    {
        if (isset($data['idpages']) && ctype_digit((string)$data['idpages'])) {
            $sql = 'UPDATE pages SET title = ?, content = ?, published = ?  WHERE idpages = ?';
            $this->_db->executeQuery(
                $sql, array(
                    $this->_app->escape($data['title']),
                    $this->_app->escape($data['content']),
                    $this->_app->escape($data['published']),
                    $this->_app->escape($data['idpages']),
                )
            );
            return 0; // else returning status 1.
        } else {
            return 1;
        }

    }

    /**
     * Creates new page using details from the form.
     *
     * @return bool
     */
    public function newPage($data)
    {
        $sql = 'INSERT INTO pages (`title`, `content`, `published`,`idusers` ) VALUES (?, ?, ?,?) ';
        //$sql2 = 'SELECT @@IDENTITY';

        $success = $this->_db->executeUpdate(
            $sql, array(
                $this->_app->escape($data['title']),
                $this->_app->escape($data['content']),
                $this->_app->escape($data['published']),
                1
            )
        );

        $identity = NULL;
        $identity = $this->_db->lastInsertId();

        if ($success) {
            return $identity;
        } else {
            return NULL;
        }

    }

    /**
     * Deletes menu entry using given id.
     *
     * @param id
     * @return bool
     */
    public function deletePage($id)
    {
        if (isset($id) && ctype_digit((string)$id)) {
            $sql = 'DELETE FROM pages WHERE idpages = ?';

            $success = $this->_db->executeQuery($sql, array($this->_app->escape($id)));

            if ($success) {
                return 0;
            } else {
                return 1;
            }
        }
    }

}