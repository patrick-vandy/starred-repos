<?php

namespace framework\model;

use framework\core\DB;
use framework\core\Model;


/**
 * GitHub model for saving and retrieving repositories.
 *
 * @package framework\model
 */
class GitHub extends Model
{

    /**
     * @var \framework\core\PDO
     */
    private $db;

    /**
     * @var \PDOStatement
     */
    private $save_stmt;


    /**
     * GitHub constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = DB::connect();
    }


    /**
     * Get list of repos.
     *
     * @param int $page
     * @param int $rpp
     * @param string $sort
     * @return \PDOStatement
     */
    public function get_repos($page = 1, $rpp = 20, $sort = 'stars DESC')
    {
        $page = (int) $page;
        $rpp = (int) $rpp;
        $offset = ($page - 1) * $rpp;

        if (!in_array($sort, ['name', 'name desc', 'description', 'description desc', 'stars', 'stars desc']))
        {
            $sort = 'stars desc';
        }

        $sql = "SELECT
                    repo_id,
                    name,
                    CASE
                        WHEN LENGTH(description) <= 100 THEN description
                        ELSE CONCAT(SUBSTRING(description, 1, 100), '...')
                    END AS description,
                    stars
                FROM repo
                ORDER BY $sort
                LIMIT $rpp OFFSET $offset";

        return $this->db->query($sql);
    }


    /**
     * Get the number of repos in the database.
     *
     * @return integer
     */
    public function get_number_repos()
    {
        $sql = 'SELECT COUNT(*) AS num_repos FROM repo';
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch()['num_repos'];
    }


    /**
     * Get details for a repo.
     *
     * @param $repo_id
     * @return array
     */
    public function get_repo($repo_id)
    {
        $sql = "SELECT
                    sync_id,
                    name,
                    description,
                    url,
                    DATE_FORMAT(repo_created_on, '%M %D, %Y at %r') AS repo_created_on,
                    DATE_FORMAT(repo_updated_on, '%M %D, %Y at %r') AS repo_updated_on,
                    DATE_FORMAT(last_push_date, '%M %D, %Y at %r') AS last_push_date,
                    stars
                FROM repo
                WHERE repo_id = ?";

        $stmt = $this->db->query($sql, [$repo_id]);
        return $stmt->fetch();
    }


    /**
     * Create a new repo_import and return its id.
     *
     * @return integer
     */
    public function get_import_id()
    {
        $sql = 'INSERT INTO repo_import (created_on) VALUES (NOW())';
        $this->db->query($sql);

        $sql = 'SELECT LAST_INSERT_ID() AS id';
        $resp = $this->db->query($sql);
        return $resp->fetch()['id'];
    }


    /**
     * Saves a repo by inserting or updating based on the sync_id.
     *
     * $fields MUST contain the following keys:
     *
     *    sync_id
     *    name
     *    description
     *    url
     *    repo_created_on
     *    repo_updated_on
     *    last_push_date
     *    stars
     *
     * @param integer $import_id
     * @param array $fields
     * @return int
     */
    public function import_repo($import_id, $fields)
    {
        if (empty($this->save_stmt))
        {
            $sql = 'CALL repo_import
                    (
                        :repo_import_id,
                        :sync_id,
                        :name,
                        :description,
                        :url,
                        :repo_created_on,
                        :repo_updated_on,
                        :last_push_date,
                        :stars
                    )';
            $this->save_stmt = $this->db->prepare($sql);
        }

        $this->save_stmt->bindParam(':repo_import_id', $import_id, \PDO::PARAM_INT);
        $this->save_stmt->bindParam(':sync_id', $fields['sync_id'], \PDO::PARAM_INT);
        $this->save_stmt->bindParam(':name', $fields['name']);
        $this->save_stmt->bindParam(':description', $fields['description']);
        $this->save_stmt->bindParam(':url', $fields['url']);
        $this->save_stmt->bindParam(':repo_created_on', $fields['repo_created_on']);
        $this->save_stmt->bindParam(':repo_updated_on', $fields['repo_updated_on']);
        $this->save_stmt->bindParam(':last_push_date', $fields['last_push_date']);
        $this->save_stmt->bindParam(':stars', $fields['stars'], \PDO::PARAM_INT);

        $this->save_stmt->execute();
    }


    /**
     * Delete repos from previous imports that were not part of the
     * import for $import_id
     *
     * @param $import_id
     * @return integer
     */
    public function delete_old_repos($import_id)
    {
        $sql = 'DELETE r
                FROM repo r
                LEFT JOIN repo_import_object o ON o.repo_id = r.repo_id
                        AND o.repo_import_id = ?
                WHERE o.repo_id IS NULL';

        $stmt = $this->db->query($sql, [$import_id]);
        return $stmt->rowCount();
    }

}