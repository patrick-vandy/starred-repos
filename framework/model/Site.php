<?php

namespace framework\model;

use framework\core\Controller;
use framework\core\ExceptionPageNotFound;
use framework\core\Model;
use framework\core\DB;


/**
 * Main model
 *
 * @package framework\model
 */
class Site extends Model
{

    /**
     * @var \framework\core\PDO
     */
    private $db;

    /**
     * @var Controller
     */
    private $controller;


    /**
     * Site constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = DB::connect();
        $this->controller = new Controller();
    }


    /**
     * @param $page_key
     * @param array $values
     * @return mixed
     * @throws ExceptionPageNotFound
     */
    public function get_page($page_key, $values = [])
    {
        $sql = 'SELECT p.page_id, p.title, p.description, t.file_name AS template
                FROM page p
                LEFT JOIN template t USING (template_id)
                WHERE p.page_key = ?
                    AND p.enabled';
        $stmt = $this->db->query($sql, [$page_key]);
        $row = $stmt->fetch();
        if (!$row)
        {
            throw new ExceptionPageNotFound("Page $page_key not found!");
        }
        $row['content'] = $this->_get_page_content($page_key, $values);
        return $row;
    }


    /**
     * @param int $parent_page_id
     * @return \PDOStatement
     */
    public function get_pages($parent_page_id = 0)
    {
        if (empty($parent_page_id))
        {
            $sql = 'SELECT page_id, page_key, route, title
                    FROM page
                    WHERE parent_page_id IS NULL
                        AND enabled
                        AND show_in_menu
                    ORDER BY sort_key';
            $stmt = $this->db->query($sql);
        }
        else
        {
            $sql = 'SELECT page_id, page_key, title
                    FROM page
                    WHERE parent_page_id = ?
                        AND enabled
                        AND show_in_menu
                    ORDER BY sort_key';
            $stmt = $this->db->query($sql, [$parent_page_id]);
        }

        return $stmt;
    }


    /**
     * @param $page_key
     * @param $values
     * @return string
     * @throws \Exception
     */
    private function _get_page_content($page_key, $values)
    {
        return $this->controller->load_view("page/$page_key", $values);
    }

}