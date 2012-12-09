<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use PDO;
use PDOException;
use RuntimeException;

/**
 * The Database class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Database
{
    /**
     * Current connection
     *
     * @access  public
     * @var     object
     */
    public $connection;

    /**
     * Array of settins for connecting to the database 
     *
     * @access  public
     * @var     array
     */
    public $settings = array();

    /**
     * Set the settings array, connect to the database 
     *
     * @access  public
     * @param   array   $settings   Array of database settings
     * @param   boolean $connect    If we should connect at object creation or not
     * @return  void
     */
    public function __construct($settings, $connect = true)
    {
        if(is_string($settings))
        {
            extract(parse_url($settings));

            $settings = array(
                'dsn' => sprintf('%s:host=%s;port=%s;dbname=%s',
                    $scheme, $host, $port, trim($path, '/')),
                'username' => $user,
                'password' => $pass
            );
        }

        $this->settings = $settings;

        if($connect)
        {
            $this->connect();
        }
    }

    /**
     * Make a connectio to the database
     *
     * @access  public
     * @throws  RuntimeException    If the connection fails
     * @return  void
     */
    public function connect()
    {
        $driverOptions = array(
            // PDO::ATTR_PERSISTENT => $this->settings['persistent'],
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );

        try
        {
            $this->connection = new PDO(
                $this->settings['dsn'],
                $this->settings['username'],
                $this->settings['password'],
                $driverOptions
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Begin a transaction
     *
     * @access  public
     * @return  boolean
     */
    public function begin()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @access  public
     * @return  boolean
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Roll back a transaction
     *
     * @access  public
     * @return  boolean
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * Fetch all results from a query returned as an array of stdClasses
     *
     * @access  public
     * @param   string      $statement  The query with named/mark placeholders
     * @param   array       $bindings   Array of values for the placeholders
     * @return  object|false            Return the result (object) or false if the query failed
     */
    public function fetchAll($statement, array $bindings = array())
    {
        $sth = $this->execute($statement, $bindings);
        $result = $sth->fetchAll(PDO::FETCH_CLASS);

        $sth->closeCursor();

        unset($sth);

        return empty($result) ? false : $result;
    }

    /**
     * Fetch first result from a query returned as an stdClass
     *
     * @access  public
     * @param   string      $statement  The query with named/mark placeholders
     * @param   array       $bindings   Array of values for the placeholders
     * @return  object|false            Return the result (object) or false if the query failed
     */
    public function fetchFirst($statement, array $bindings = array())
    {
        $sth = $this->execute($statement, $bindings);
        $result = $sth->fetchObject();

        $sth->closeCursor();

        unset($sth);

        return is_object($result) ? $result : false;
    }

    /**
     * Return number of rows from a query
     *
     * @access  public
     * @param   string      $statement  The query with named/mark placeholders
     * @param   array       $bindings   Array of values for the placeholders
     * @return  int|false               Return the number of rows or false if the query failed
     */
    public function rows($statement, array $bindings = array())
    {
        $sth = $this->execute($statement, $bindings);
        $result = $sth->rowCount();

        $sth->closeCursor();

        unset($sth);

        return ($result > 0) ? $result : false;
    }

    /**
     * Perform an insert query
     *
     * @access  public
     * @param   string      $table      The table name
     * @param   array       $columns    The table columns
     * @param   array       $bindings   Array of values to be inserted
     * @return  int|false               Return the row count or false if the query failed
     */
    public function insert($table, array $columns, array $bindings)
    {
        $values = implode(', ', array_fill(0, (count($bindings) / count($columns)),
            sprintf('(%s?)', str_repeat('?, ', (count($columns) - 1)))));

        $statement = sprintf('INSERT INTO %s (%s) VALUES %s',
            $table, implode(', ', $columns), $values);

        $sth = $this->execute($statement, $bindings);
        $result = $sth->rowCount();

        $sth->closeCursor();

        unset($sth);

        return ($result > 0) ? $result : false;
    }

    /**
     * Perform an update query
     *
     * @access  public
     * @param   string      $table      The table name
     * @param   string      $where      The 'WHERE' clause in the sql query
     * @param   array       $columns    The talbe columns
     * @param   array       $bindings   Array of values to be updated
     * @return  int|false               Return the row count or false if the query failed
     */
    public function update($table, $where, array $columns, array $bindings)
    {
        $statement = sprintf('UPDATE %s SET %s WHERE %s',
            $table, implode(' = ?, ', $columns) . ' = ?', $where);

        $sth = $this->execute($statement, $bindings);
        $result = $sth->rowCount();

        $sth->closeCursor();

        unset($sth);

        return ($result > 0) ? $result : false;
    }

    /**
     * Perform an update query with cases
     *
     * @access  public
     * @param   string      $table      The table name
     * @param   string      $column     The table columns
     * @param   string      $case       The case
     * @param   array       $variables  Array of values to be updated
     * @return  int|false               Return the row count or false if the query failed
     */
    public function updateMany($table, $column, $case, array $variables)
    {
        $cases = null;

        foreach($variables as $key => $value)
        {
            $cases .= sprintf(" WHEN '%s' THEN '%s'", $key, $value);
        }

        $statement = sprintf('UPDATE %s SET %s = CASE %s %s ELSE %s END', $table, $column, $case, $cases, $column);

        $sth = $this->execute($statement, array());
        $result = $sth->rowCount();

        $sth->closeCursor();

        unset($sth);

        return ($result > 0) ? $result : false;
    }

    /**
     * Perform a delete query
     *
     * @access  public
     * @param   string      $statement  The query with named/mark placeholders
     * @param   array       $bindings   Array of values for the placeholders
     * @return  int|false               Return the row count or false if the query failed
     */
    public function delete($statement, array $bindings = array())
    {
        // TODO make it easier to write a delete query (like insert/update)
        return $this->rows($statement, $bindings);
    }

    /**
     * Execute a prepared statement
     *
     * @access  public
     * @param   string  $statement  The query with named/mark placeholders
     * @param   array   $bindings   Array of values for the placeholders
     * @return  object
     */
    protected function execute($statement, array $bindings = array())
    {
        $sth = $this->connection->prepare($statement);

        $sth->execute($bindings);

        return $sth;
    }
    
    /**
     * Shorthand function to perform a query transaction
     *
     * @access  public
     * @param   mixed   $query  The query to be performed
     * @return  boolean         Return true if it worked (then commit) else return false (and rollback)
     */
    public function transaction($query)
    {
        $this->begin();

        if( ! $query)
        {
            $this->rollBack();

            return false;
        }

        $this->commit();

        return true;
    }

    /**
     * Closes the current database connection
     *
     * @access  public
     * @return  void
     */
    public function close()
    {
        $this->connection = null;
    }
}
