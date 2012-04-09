<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

/**
 * The Session class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Session
{
    /**
     * Define the session name at object creation (optional)
     *
     * @access  public
     * @param   string  $name   The session name
     * @return  void
     */
    public function __construct($name = null)
    {
        session_start();

        if($name)
        {
            session_name($name);
        }
    }

    /**
     * Set or get the session name
     *
     * @access  public
     * @param   string      $name   The session name
     * @return  string|void         Return the session name, or set the session name
     */
    public function name($name = null)
    {
        return ($name) ? session_name($name) : session_name();
    }

    /**
     * Set or get the session id
     *
     * @access  public
     * @param   string      $id The session name
     * @return  string|void     Return the session name, or set the session name
     */
    public function id($id = null)
    {
        return ($id) ? session_id($id) : session_id();
    }

    /**
     * Write data and end session
     *
     * @access  public
     * @return  void
     */
    public function close()
    {
        session_write_close();
    }

    /**
     * Destroy the current session and unset the session array
     *
     * @access  public
     * @return  void
     */
    public function destroy()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Set a session variable
     *
     * @access  public
     * @param   string  $key    The session variable identifier
     * @param   mixed   $value  The session variable value
     * @return  void
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable value
     *
     * @access  public
     * @param   string      $key    The session variable identifier
     * @return  string|null         If the variable is set return it, else return null
     */
    public function get($key)
    {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : null;
    }

    /**
     * Unset a session variable
     *
     * @access  public
     * @param   string  $key    The session variable identifier
     * @return  void
     */
    public function del($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Check if a session variable has been set
     *
     * @access  public
     * @param   string  $key    The session variable identifier
     * @return  boolean         Returns true if it has been set, else false
     */
    public function has($key)
    {
        return (bool) isset($_SESSION[$key]);
    }

    /**
     * Get the session token
     *
     * @access  public
     * @param   string  $token  The session token identifier
     * @return  string          The session token value
     */
    public function token($token = 'token')
    {
        return $this->get($token);
    }

    /**
     * Generate a session token
     *
     * @access  public
     * @param   string  $token  The session token identifier
     * @return  void
     */
    public function generate($token = 'token')
    {
        $_SESSION[$token] = sha1(time() . uniqid(rand(), true));
    }

    /**
     * Compare a value to the session token
     *
     * @access  public
     * @param   string  $input  The input value
     * @param   string  $token  The session token identifier
     * @return  boolean         Return true if it is the same, else false
     */
    public function compare($input, $token = 'token')
    {
        return (bool) ($input == $this->token($token));
    }

    /**
     * Refresh the session with a new id and a new session token
     *
     * ex. when a user signs in, refresh the session
     *
     * @access  public
     * @param   string  $token  The session token name
     * @return  void
     */
    public function refresh($token = 'token')
    {
        session_regenerate_id(true);
        session_unset();
        $this->generate($token);
    }

    /**
     * Set a session variable
     *
     * @access  public
     * @param   string  $key    The session variable identifier
     * @param   mixed   $value  The session variable value
     * @return  void
     */
    public function __set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable value
     *
     * @access  public
     * @param   string      $key    The session variable identifier
     * @return  string|null         If the variable is set return it, else return null
     */
    public function __get($key)
    {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : null;
    }

    /**
     * Check if a session variable has been set
     *
     * @access  public
     * @param   string  $key    The session variable identifier
     * @return  boolean         Returns true if it has been set, else false
     */
    public function __isset($key)
    {
        return (bool) isset($_SESSION[$key]);
    }

    /**
     * Unset a session variable
     *
     * @access  public
     * @param   string  $key    The session variable identifier
     * @return  void
     */
    public function __unset($key)
    {
        unset($_SESSION[$key]);
    }
}
