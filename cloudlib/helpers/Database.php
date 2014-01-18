<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2013 Sebastian Bengtegård <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use PDO;
use PDOException;
use RuntimeException;

/**
 * The Database class
 *
 * @copyright   Copyright (c) 2013 Sebastian Bengtegård <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Database
{
    /**
     * The Database connection
     *
     * @access  public
     * @var     object
     */
    public $connection = null;

    /**
     * Database connection settings
     *
     * @access  public
     * @var     array
     */
    public $settings = array();

    /**
     * Current Database statement (query)
     *
     * @access  public
     * @var     string
     */
    public $statement = '';

    /**
     * PDO Bindings for prepared statements
     *
     * @access  public
     * @var     array
     */
    public $bindings = array();

    /**
     * The current statement handler
     *
     * @access  public
     * @var     object
     */
    public $statementHandler = null;

    /**
     * Array of error information
     *
     * @access  public
     * @var     array
     */
    public $errorInfo = null;

    /**
     * Error code of database exception
     *
     * @access  public
     * @var     int
     */
    public $errorCode = null;

    /**
     * PDO Exception
     *
     * @access  public
     * @var     object
     */
    public $exception = null;

    /**
     * Method to be used when executing a database statement
     *
     * @access  public
     * @var     string
     */
    public $method = false;

    /**
     * Array of columns for the current query
     *
     * @access  public
     * @var     array
     */
    public $columns = array();

    /**
     * Initialize the database connection
     *
     * @access  public
     * @param   mixed   $settings   Array or String to be used to connect to the database
     * @param   bool    $wait       If we should wait with connecting to the database
     * @return  void
     */
    public function __construct($settings, $wait = false)
    {
        if(is_string($settings))
        {
            try
            {
                extract(parse_url($settings));

                $settings = array(
                    'dsn' => sprintf('%s:host=%s;port=%s;dbname=%s',
                        $scheme, $host, $port, trim($path, '/')),
                    'username' => $user,
                    'password' => $pass
                );
            }
            catch(Exception $e)
            {
                throw new InvalidArgumentException(
                    sprintf('Invalid Database URL [%]', $settings)
                );
            }
        }

        $this->settings = $settings;

        if( ! $wait)
        {
            $this->connect();
        }
    }

    /**
     * Initialize a connection to a database
     *
     * @access  public
     * @param   mixed   $settings   Array or String to be used to connect to the database
     * @return  object              Returns the database connection
     */
    public function connect($settings = null)
    {
        $settings = $settings ? $settings : $this->settings;

        $driverOptions = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );

        try
        {
            $this->connection = new PDO(
                $settings['dsn'],
                $settings['username'],
                $settings['password'],
                $driverOptions
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->connection;
        }
        catch(PDOException $e)
        {
            throw new RuntimeException('Unable to establish database connection');
        }
    }

    /**
     * Shorthand method for beginTransaction()
     *
     * @access  public
     * @return  bool
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Shorthand method for rollBack()
     *
     * @access  public
     * @return  bool
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }

    /**
     * Shorthand method for commit()
     *
     * @access  public
     * @return  bool
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Shorthand method for isTransaction()
     *
     * @access  public
     * @return  bool
     */
    public function inTransaction()
    {
        return $this->connection->inTransaction();
    }

    /**
     * Shorthand method for lastInsertId()
     *
     * @access  public
     * @return  bool
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Shorthand method for errorInfo()
     *
     * @access  public
     * @return  array
     */
    public function errorInfo()
    {
        return $this->connection->errorInfo();
    }

    /**
     * Shorthand method for errorCode()
     *
     * @access  public
     * @return  int
     */
    public function errorCode()
    {
        return $this->connection->errorCode();
    }

    /**
     * Fetch all results from a query
     *
     * @access  public
     * @param   string  $statement  Database query
     * @param   array   $bindings   Bindings used for prepared statements
     * @return  object
     */
    public function fetchAll($statement, array $bindings = array())
    {
        $this->reset();

        $this->statement = $statement;
        $this->bindings = $bindings;
        $this->prepare($statement);
        $this->method = 'fetchAll';
        return $this;
    }

    /**
     * Fetch the first result from a query
     *
     * @access  public
     * @param   string  $statement  Database query
     * @param   array   $bindings   Bindings used for prepared statements
     * @return  object
     */
    public function fetchFirst($statement, array $bindings = array())
    {
        $this->reset();

        $this->statement = $statement;
        $this->bindings = $bindings;
        $this->prepare($statement);
        $this->method = 'fetch';
        return $this;
    }

    /**
     * Perform a custom query
     *
     * @access  public
     * @param   string  $statement  Database query
     * @param   array   $bindings   Bindings used for prepared statements
     * @return  object
     */
    public function query($statement, array $bindings = array())
    {
        $this->reset();

        $this->statement = $statement;
        $this->bindings = $bindings;
        $this->prepare($statement);
        return $this;
    }

    /**
     * Determine if we are performing an INSERT query on a single object or multiple
     *
     * @access  public
     * @param   mixed   $object The object(s)
     * @param   string  $table  The database table
     * @return  object
     */
    public function create($object, $table)
    {
        $this->reset();

        $this->setColumns($object);

        if(is_object($object))
        {
            $this->insertObject($object, $table);
        }
        elseif(is_array($object))
        {
            $this->insertObjects($object, $table);
        }

        return $this;
    }

    /**
     * Determine if we are performing an UPDATE query on a single object or multiple
     *
     * @access  public
     * @param   mixed   $object The object(s)
     * @param   string  $table  The database table
     * @param   string  $id     Object identifier
     * @return  object
     */
    public function update($object, $table, $id = 'id')
    {
        $this->reset();

        $this->setColumns($object);
    
        if(is_object($object))
        {
            $this->updateObject($object, $table, $id);
        }
        elseif(is_array($object))
        {
            $this->updateObjects($object, $table, $id);
        }

        return $this;
    }

    /**
     * Determine if we are performing an DELETE query on a single object or multiple
     *
     * @access  public
     * @param   mixed   $object The object(s)
     * @param   string  $table  The database table
     * @param   string  $id     Object identifier
     * @return  object
     */
    public function remove($object, $table, $id = 'id')
    {
        $this->reset();

        $this->setColumns($object);

        if(is_object($object))
        {
            $this->deleteObject($object, $table, $id);
        }
        elseif(is_array($object))
        {
            $this->deleteObjects($object, $table, $id);
        }

        return $this;
    }

    /**
     * Perform an INSERT query on a single object
     *
     * @access  public
     * @param   object  $object The object
     * @param   string  $table  The database table
     * @return  object
     */
    public function insertObject($object, $table)
    {
        $columns = implode(', ', $this->columns);
        $placeholders = str_repeat('?, ', (count($this->columns) - 1)) . '?';

        $statement = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $table, $columns, $placeholders);

        $this->statement = $statement;
        $this->bindings = array_values(get_object_vars($object));
        $this->prepare($statement);
        
        return $this;
    }

    /**
     * Perform an INSERT query on multiple objects
     *
     * @access  public
     * @param   object  $objects    The objects
     * @param   string  $table      The database table
     * @return  object
     */
    public function insertObjects(array $objects, $table)
    {
        $columns = implode(', ', $this->columns);   

        $bindings = $placeholders = array();

        foreach($objects as $object)
        {
            $placeholders[] = sprintf('(%s?)',
                str_repeat('?, ', (count($this->columns) - 1))
            );

            $bindings = array_merge($bindings,
                array_values(get_object_vars($object))
            );
        }

        $placeholders = implode(', ', $placeholders);

        $statement = sprintf('INSERT INTO %s (%s) VALUES %s',
            $table, $columns, $placeholders);

        $this->statement = $statement;
        $this->bindings = $bindings;
        $this->prepare($statement);

        return $this;
    }

    /**
     * Perform an UPDATE query on an object
     *
     * @access  public
     * @param   object  $object The object
     * @param   string  $table  The database table
     * @param   string  $id     Object identifier
     * @return  object
     */
    public function updateObject($object, $table, $id = 'id')
    {
        $columns = $this->columns;
        $key = array_search($id, $columns);
        unset($columns[$key]);

        $placeholders = implode(' = ?, ', $columns) . ' = ?';

        $statement = sprintf('UPDATE %s SET %s WHERE %s = ?',
            $table, $placeholders, $id);

        $values = get_object_vars($object);
        $tmp = $values[$id];
        unset($values[$id]);
        $values[$id] = $tmp;

        $this->statement = $statement;
        $this->bindings = array_values($values);
        $this->prepare($statement);

        return $this;
    }

    /**
     * Perform an UPDATE query on multiple objects
     *
     * @access  public
     * @param   object  $objects    The objects
     * @param   string  $table      The database table
     * @param   string  $id         Object identifier
     * @return  object
     */
    public function updateObjects(array $objects, $table, $id = 'id')
    {
        $columns = $this->columns;
        $key = array_search($id, $columns);
        unset($columns[$key]);

        $bindings = $cases = $ids = array();

        foreach($objects as $object)
        {
            array_push($ids, $object->id);
        }

        foreach($columns as $column)
        {
            $case = sprintf('%s = CASE %s ', $column, $id);

            foreach($objects as $object)
            {
                if(property_exists($object, $column))
                {
                    $case .= 'WHEN ? THEN ? ';
                    array_push($bindings, $object->{$id}, $object->{$column});
                }
            }

            $case .= 'END';
            array_push($cases, $case);
        }

        $cases = implode(', ', $cases);
        $ids = implode(', ', $ids);
        
        $statement = sprintf('UPDATE %s SET %s WHERE %s IN (%s)',
            $table, $cases, $id, $ids);

        $this->statement = $statement;
        $this->bindings = $bindings;
        $this->prepare($statement);

        return $this;
    }

    /**
     * Perform an DELETE query on an object
     *
     * @access  public
     * @param   object  $object The object
     * @param   string  $table  The database table
     * @param   string  $id     Object identifier
     * @return  object
     */
    public function deleteObject($object, $table, $id = 'id')
    {
        $statement = sprintf('DELETE FROM %s WHERE %s = ?', $table, $id);

        $this->statement = $statement;
        $this->bindings = array($object->{$id});
        $this->prepare($statement);

        return $this;
    }

    /**
     * Perform an DELETE query on multiple objects
     *
     * @access  public
     * @param   object  $objects    The objects
     * @param   string  $table      The database table
     * @param   string  $id         Object identifier
     * @return  object
     */
    public function deleteObjects(array $objects, $table, $id = 'id')
    {
        $placeholders = $bindings = array();

        foreach($objects as $object)
        {
            array_push($placeholders, '?');
            array_push($bindings, $object->{$id});
        }

        $placeholders = implode(', ', $placeholders);

        $statement = sprintf('DELETE FROM %s WHERE %s IN (%s)',
            $table, $id, $placeholders);

        $this->statement = $statement;
        $this->bindings = $bindings;
        $this->prepare($statement);

        return $this;
    }

    // TODO: implement..
    public function where()
    {
        // ...
    }

    /**
     * Add an ORDER BY to the current query
     *
     * @access  public
     * @param   string  $order  The 'ORDER BY' statement
     * @return  object
     */
    public function order($order)
    {
        $order = sprintf(' ORDER BY %s', $order);
        $this->statement .= $order;
        $this->prepare();

        return $this;
    }

    /**
     * Add an LIMIT to the current query
     *
     * @access  public
     * @param   int     $limit  The limit
     * @param   int     $offset The offset
     * @return  object
     */
    public function limit($limit, $offset = null)
    {
        $limit = $offset !== null ? sprintf(' LIMIT %s, %s', $limit, $offset) : sprintf(' LIMIT %s', $limit);
        $this->statement .= $limit;
        $this->prepare();
        
        return $this;
    }

    /**
     * Defines the columns to be used in queries based off of the object properties
     *
     * @access  public
     * @param   object  $object Object(s) to be used for database querying
     * @return  void
     */
    public function setColumns($object)
    {
        $object = is_array($object) ? $object[0] : $object;
        $this->columns = array_keys(get_object_vars($object));
    }

    /**
     * Prepare a database statement for execution
     *
     * @access  public
     * @param   string  $statement  The statement to be prepared
     * @return  void
     */
    public function prepare($statement = null)
    {
        $statement = $statement ? $statement : $this->statement;

        try
        {
            $this->statementHandler = $this->connection->prepare($statement);
        }
        catch(PDOException $e)
        {
            throw new RuntimeException(
                sprintf('Unable to prepare statement [%s]', $statement)
            );
        }
    }

    /**
     * Execute a prepared database statement
     *
     * @access  public
     * @param   array   $bindings   Bindings to be used with the prepared statement
     * @return  mixed               Returns the resultset or false if it failed
     */
    public function execute(array $bindings = array())
    {
        $bindings = $bindings ? $bindings : $this->bindings;

        try
        {
            $this->statementHandler->execute($bindings);
        }
        catch(PDOException $e)
        {
            if( (bool) $this->inTransaction())
            {
                $this->rollBack();
            }

            $this->errorInfo = $this->statementHandler->errorInfo();
            $this->errorCode = $this->statementHandler->errorCode();
            $this->exception = $e;

            return false;
        }

        if( (bool) $this->inTransaction())
        {
            $this->commit();
        }

        if($this->method)
        {
            $this->statementHandler->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
            $result = call_user_func(array($this->statementHandler, $this->method));
        }
        else
        {
            $result = $this->statementHandler->rowCount();
        }

        $this->statementHandler->closeCursor();
        $this->statementHandler = null;

        return $result;
    }

    /**
     * Start a database transaction
     *
     * @access  public
     * @return  object
     */
    public function transaction()
    {
        $this->beginTransaction();
        return $this;
    }

    /**
     * Close the database connection
     *
     * @access  public
     * @return  void
     */
    public function close()
    {
        $this->statementHandler = null;
        $this->connection = null;
    }

    /**
     * Resets database properties before issuing a new query
     *
     * @access  public
     * @return  void
     */
    public function reset()
    {
        $this->statement = '';
        $this->bindings = array();
        $this->statementHandler = null;
        $this->errorInfo = null;
        $this->errorCode = null;
        $this->exception = null;
        $this->method = false;
        $this->columns = array();
    }
}
