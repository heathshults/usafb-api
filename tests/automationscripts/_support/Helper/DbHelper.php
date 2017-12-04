<?php

namespace Helper;


use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Db as Driver;


class DbHelper extends \Codeception\Module
{

    protected $dbh;

    /**
     * Function to connect to DB
     * @param $dsn
     * @param $username
     * @param $password
     */
    private function customConnect($dsn,$username,$password)
    {
        try {
            $this->dbh = new \PDO($dsn, $username, $password );

        } catch (\PDOException $e) {
            $message = $e->getMessage();
        }

    }

    /**
     * Function to Disconnect the Db connection
     */
    private function customDisconnect()
    {
        $this->debugSection('Db', 'Disconnected');
        $this->dbh = null;
        $this->driver = null;
    }

    /**
     * Function to execute custom queries and return the rows as Array
     * @param $dsn
     * @param $username
     * @param $password
     * @param $query
     * @return mixed
     */
    public function customSqlQuery($dsn,$username,$password,$query)
    {
        codecept_debug($query);
        $this->customConnect($dsn,$username,$password);
        $sql = $query;
        $result = $this->dbh ->query($sql);

        $this->customDisconnect();
        return($result->fetchAll(\PDO::FETCH_ASSOC));
    }
}
