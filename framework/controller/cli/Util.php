<?php

namespace framework\controller\cli;

use framework\core\Config;
use framework\core\Controller;
use framework\model\GitHubAPI;
use framework\model\GitHub;


/**
 * Util class for importing repositories from the GitHub API.
 *
 * @package framework\controller\cli
 */
class Util extends Controller
{

    /**
     * @var GitHubAPI
     */
    private $github_api;

    /**
     * @var GitHub
     */
    private $github;

    private $max_repos;


    /**
     * Util constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->github_api = new GitHubAPI();
        $this->github = new GitHub();
        $this->max_repos = Config::get('max_repos');
    }


    /**
     * Imports the repos from GitHub API and deletes any existing repos
     * from previous imports.
     *
     * $max_repos can be passed in to override the config value max_repos.
     *
     * @param int $max_repos
     */
    public function import($max_repos = 0)
    {
        if ($max_repos)
        {
            $this->max_repos = $max_repos;
        }

        $import_id = $this->github->get_import_id();

        echo "\n Importing repos";
        $count = $this->_get_repos($import_id);
        echo "\n $count repos imported";

        $count = $this->github->delete_old_repos($import_id);
        echo "\n $count repos from previous imports deleted\n\n";
    }


    /**
     * Gets the most starred repos from the GitHub API in descending order
     * and imports them into the database.
     *
     * If the response headers contain a next link (meaning there are more
     * results) recursion is used until there is not a next link or $count
     * exceeds $this->max_repos.
     *
     * @param $import_id
     * @param bool|false $url
     * @param int $count
     * @return int
     * @throws \Exception
     */
    private function _get_repos($import_id, $url = false, $count = 0)
    {
        if (!$url)
        {
            $response = $this->github_api->search_repos('language:php', 'stars', 'desc');
        }
        else
        {
            $response = $this->github_api->make_request($url);
        }

        $num_items = count($response['body']['items']);
        for ($i = 0; $i < $num_items && $count < $this->max_repos; $i++)
        {
            $fields = [
                'sync_id' => $response['body']['items'][$i]['id'],
                'name' => $response['body']['items'][$i]['name'],
                'description' => $response['body']['items'][$i]['description'],
                'url' => $response['body']['items'][$i]['html_url'],
                'repo_created_on' => $response['body']['items'][$i]['created_at'],
                'repo_updated_on' => $response['body']['items'][$i]['updated_at'],
                'last_push_date' => $response['body']['items'][$i]['pushed_at'],
                'stars' => $response['body']['items'][$i]['stargazers_count']
            ];
            $this->github->import_repo($import_id, $fields);
            $count++;
        }

        echo ".";

        if (!empty($response['headers']['link']['next']) && $count < $this->max_repos)
        {
            $count = $this->_get_repos($import_id, $response['headers']['link']['next'], $count);
        }

        return $count;
    }

}