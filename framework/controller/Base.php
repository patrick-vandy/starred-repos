<?php

namespace framework\controller;

use framework\core\Controller;
use framework\core\Config;
use framework\model\Site as SiteModel;

/**
 * Provides additional functionality needed in all Web Controllers.
 *
 * @package framework\controller
 */
class Base extends Controller
{

    protected $site_model;


    /**
     * Base constructor.
     *
     * Start session here so it is always available if needed.
     */
    public function __construct()
    {
        parent::__construct();
        $this->site_model = new SiteModel();
        $this->_start_session();
    }


    /**
     * Load a given page and exit.
     *
     * $page is an array of name/value pairs to replace the placeholders
     * in the template. You can specify template as one of the name/value
     * pairs in the array or the default template will be used.
     *
     * @param $page
     * @throws \Exception
     */
    protected function _load_page($page)
    {
        $template = !empty($page['template']) ? $page['template'] : Config::get('default_template');
        unset($page['template']);

        $page['menu'] = $this->_build_menu();
        $page['footer_nav'] = $this->_build_menu(0, false);

        echo $this->load_view("template/$template", $page);
        exit;
    }


    /**
     * Build the html for the menu.
     *
     * @param int $parent_page_id
     * @param bool|true $build_sub_menus
     * @return string
     * @throws \Exception
     */
    private function _build_menu($parent_page_id = 0, $build_sub_menus = true)
    {
        $menu = '';

        $pages = $this->site_model->get_pages($parent_page_id);
        while ($page = $pages->fetch())
        {
            $sub_menu = '';
            if ($build_sub_menus)
            {
                $sub_menu = $this->_build_menu($page['page_id']);
            }

            $values = [
                'class' => $page['page_key'],
                'link' => $page['route'],
                'item' => $page['title'],
                'sub_list' => $sub_menu
            ];

            $menu .= $this->load_view("piece/list/li-a-class", $values);
        }

        if (!empty($menu))
        {
            $menu = $this->load_view('piece/list/ul', ['items' => $menu]);
        }

        return $menu;
    }


    /**
     * Start session if it is not already started.
     */
    protected function _start_session()
    {
        if (!isset($_SESSION))
        {
            session_start();
        }
    }

}