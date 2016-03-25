<?php

namespace framework\controller;
use framework\core\Config;
use framework\core\Input;
use framework\model\GitHub;


/**
 * Home controller.
 *
 * This is the main controller that contains the public facing pages.
 *
 * @package framework\controller
 */
class Home extends Base
{

    /**
     * @var GitHub
     */
    private $github;


    /**
     * Home constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->github = new GitHub();
    }


    /**
     * page: /home/index
     *
     * The main index page.
     *
     * @throws \Exception
     * @throws \framework\core\ExceptionPageNotFound
     */
    public function index()
    {
        $page_num = (int) Input::get('page', 1, 'get');
        $rpp = (int) Input::get('rpp', Config::get('rpp'), 'get');
        $sort = Input::get('sort', 'stars desc', 'get');

        if (!$page_num)
        {
            $page_num = 1;
        }

        if (!$rpp)
        {
            $rpp = Input::get('rpp', Config::get('rpp'));
        }

        $num_repos = $this->github->get_number_repos();

        $values = [
            'repos' => $this->_repo_list($page_num, $rpp, $sort),
            'rpp' => $rpp,
            'sort' => $sort,
            'rpp_list' => $this->_rpp_list($rpp),
            'pagination' => $this->_paginate($num_repos, $page_num, $rpp, $sort)
        ];
        $page = $this->site_model->get_page('home', $values);
        $this->_load_page($page);
    }


    /**
     * page: /home/repo_details/[ID]
     *
     * Get the details for repo_id provided and output in JSON.
     *
     * @param $repo_id
     */
    public function repo_details($repo_id)
    {
        $repo = $this->github->get_repo($repo_id);
        $this->load_json($repo);
    }


    /**
     * Get the list of repos and return them formatted as html table rows.
     *
     * @param $page
     * @param $rpp
     * @param $sort
     * @return string
     * @throws \Exception
     */
    private function _repo_list($page, $rpp, $sort)
    {
        $html = '';
        $repos = $this->github->get_repos($page, $rpp, $sort);
        while ($repo = $repos->fetch())
        {
            $repo = $this->_html_escape($repo);
            $html .= $this->load_view('content/repo', $repo);
        }
        return $html;
    }


    /**
     * Get the options for the results per page drop down list.
     *
     * @param $rpp
     * @return string
     * @throws \Exception
     */
    private function _rpp_list($rpp)
    {
        $options = [10, 20, 50, 100, 250, 500];
        $html = '';
        foreach ($options as $option)
        {
            $template = ($option == $rpp) ? 'option-selected' : 'option';
            $html .= $this->load_view("piece/form/$template", ['value' => $option, 'name' => $option]);
        }
        return $html;
    }


    /**
     * Build pagination links and return as html list with links.
     *
     * @param $total
     * @param $page
     * @param $rpp
     * @param $sort
     * @return string
     * @throws \Exception
     */
    private function _paginate($total, $page, $rpp, $sort)
    {
        $html = '';

        if ($total <= 1)
        {
            return $html;
        }

        $sort = urlencode($sort);
        $url_base = "/home/index/rpp:$rpp/sort:$sort/page:";
        $num_pages = $total / $rpp;
        $offset = max($page - 5, 1);
        $prev = max($page - 1, 1);

        $template = 'li-a';
        $values = ['link' => $url_base . $prev, 'item' => '<span>&laquo;</span>'];
        if ($page == 1)
        {
            $template = 'li-a-class';
            $values['class'] = 'disabled';
        }
        $html .= $this->load_view("piece/list/$template", $values);

        for ($i = $offset, $j = 1; $i <= $num_pages && $j <= 10; $i++, $j++)
        {
            $url = $url_base . $i;
            $template = 'li-a';
            $values = ['link' => $url, 'item' => $i];
            if ($i == $page)
            {
                $template = 'li-a-class';
                $values['class'] = 'active';
            }

            $html .= $this->load_view("piece/list/$template", $values);
        }

        $template = 'li-a';
        $values = ['link' => $url_base . ($page + 1), 'item' => '<span>&raquo;</span>'];
        if ($page == $num_pages)
        {
            $template = 'li-a-class';
            $values['class'] = 'disabled';
        }
        $html .= $this->load_view("piece/list/$template", $values);

        return $html;
    }

}