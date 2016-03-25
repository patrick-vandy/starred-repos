<?php

namespace framework\core;


/**
 * Extend PDO to add some minor improvements. The following methods are overridden:
 *
 * __construct:       set error mode to exception and fetch mode to associate
 * beginTransaction:  needed for nested transactions
 * commit:            needed for nested transactions
 * rollback:          needed for nested transactions
 * query:             prevent direct queries and force prepared statement instead
 *
 * @package framework\core
 */
class PDO extends \PDO
{

    private $transactions;


    /**
     * Override PDO constructor to set exception mode for errors and fetch mode
     * to fetch associate when creating new instance.
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     */
    public function __construct($dsn, $user, $password)
    {
        parent::__construct($dsn, $user, $password);
        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
        $this->setAttribute(self::ATTR_DEFAULT_FETCH_MODE, self::FETCH_ASSOC);
        $this->transactions = 0;
    }


    /**
     * Override PDO transaction handling to deal with nested transactions.
     *
     * @return bool
     */
    public function beginTransaction()
    {
        if (!$this->transactions++)
        {
            return parent::beginTransaction();
        }
        return true;
    }


    /**
     * Override PDO transaction handling to deal with nested transactions.
     *
     * @return bool
     */
    public function commit()
    {
        if (!--$this->transactions)
        {
            return parent::commit();
        }
        return false;
    }


    /**
     * Override PDO transaction handling to deal with nested transactions.
     *
     * @return bool
     */
    public function rollback()
    {
        if ($this->transactions > 0)
        {
            $this->transactions = 0;
            return parent::rollBack();
        }
        return false;
    }


    /**
     * Override PDO::query to force use of prepared statements.
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

}