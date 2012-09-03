<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use RuntimeException;

/**
 * The Logger class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Logger
{
    /**
     * The file the logs will be written to
     *
     * @access  protected
     * @var     string
     */
    protected $file;

    /**
     * Array of log messages
     *
     * @access  protected
     * @var     array
     */
    protected $messages = array();

    /**
     * Set the file that logs will be written to at object creation
     *
     * Define wether ot not we will run write() at shutdown
     *
     * @access  public
     * @param   string  $file       The filename
     * @param   boolean $register   If we should call write() at shutdown
     * @return  void
     */
    public function __construct($file, $register = true)
    {
        $this->file = $file;

        if($register)
        {
            register_shutdown_function(array($this, 'write'));
        }
    }

    /**
     * Register the write method as a shutdown function
     *
     * @access  public
     * @return  void
     */
    public function register()
    {
        register_shutdown_function(array($this, 'write'));
    }

    /**
     * Log a message with a severity level (ex debug, info etc)
     *
     * @access  public
     * @param   string|array    $message    The message, or an array of messages
     * @param   string|int      $severity   The level of severity (ex DEBUG or 0)
     * @return  void
     */
    public function log($message, $severity = 0)
    {
        $severity = $this->getLevel($severity);

        if(is_array($message))
        {
            foreach($message as $value)
            {
                $this->messages[] = sprintf('[%s][%s]: %s', date('Y-m-d G:i:s'),
                    $severity, $value);
            }
        }
        else
        {
            $this->messages[] = sprintf('[%s][%s]: %s', date('Y-m-d G:i:s'), $severity,
                $message);
        }
    }

    /**
     * Write all log messages to the log file
     *
     * @access  public
     * @return  boolean     Returns false if there are no messages to write, true if successful
     */
    public function write()
    {
        $contents = null;

        if(empty($this->messages))
        {
            return false;
        }

        foreach($this->messages as $message)
        {
            $contents .= $message . PHP_EOL;
        }

        if($contents !== null)
        {
            try
            {
                file_put_contents($this->file, $contents, LOCK_EX | FILE_APPEND);
            }
            catch(RuntimeException $e)
            {
                throw new RuntimeException(sprintf('Unable to write to the logfile [%s]',
                    $file));
            }
            return true;
        }
    }

    /**
     * Returns the severity level as a string
     *
     * @access  protected
     * @param   int         $level  The severity level as a string (DEBUG) or int (0)
     * @return  string              The severity level returned as a string in uppercase
     */
    protected function getLevel($level = 0)
    {
        switch($level)
        {
            case 0:
            case 'DEBUG':
                return 'DEBUG';
                break;
            case 1:
            case 'INFO':
                return 'INFO';
                break;
            case 2:
            case 'WARNING':
                return 'WARNING';
                break;
            case 3:
            case 'ERROR':
                return 'ERROR';
                break;
            default:
                return 'DEBUG';
                break;
        }
    }

    /**
     * Log a debug message
     *
     * @access  public
     * @param   string|array    $message    The message, or an array of messages
     * @return  void
     */
    public function debug($message)
    {
        $this->log($message, 0);
    }

    /**
     * Log a info message
     *
     * @access  public
     * @param   string|array    $message    The message, or an array of messages
     * @return  void
     */
    public function info($message)
    {
        $this->log($message, 1);
    }

    /**
     * Log a warning message
     *
     * @access  public
     * @param   string|array    $message    The message, or an array of messages
     * @return  void
     */
    public function warning($message)
    {
        $this->log($message, 2);
    }

    /**
     * Log a error message
     *
     * @access  public
     * @param   string|array    $message    The message, or an array of messages
     * @return  void
     */
    public function error($message)
    {
        $this->log($message, 3);
    }

    /**
     * Return the current logged messages
     *
     * @access  public
     * @return  array   Returns an array of logged messages
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
