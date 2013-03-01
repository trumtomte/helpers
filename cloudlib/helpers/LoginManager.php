<?php
/**
 * Cloudlib
 * 
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use cloudlib\core\Cloudlib;
use RuntimeException;
use Closure;

/**
 * The LoginManager class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class LoginManager
{
    /**
     * Cloudlib core class
     *
     * @access  public
     * @var     object
     */
    public $app = null;

    /**
     * The login route
     *
     * @access  public
     * @var     string
     */
    public $loginRoute = null;

    /**
     * Handler to be invoked if attempt is unauthorized
     *
     * @access  public
     * @var     Closure
     */
    public $unauthorizedHandler = null;

    /**
     * Closure to be called before login
     *
     * @access  public
     * @var     Closure
     */
    public $beforeLogin = null;

    /**
     * Closure to be called after login
     *
     * @access  public
     * @var     Closure
     */
    public $afterLogin = null;

    /**
     * Closure to be called before logout
     *
     * @access  public
     * @var     Closure
     */
    public $beforeLogout = null;

    /**
     * Closure to be called after logout
     *
     * @access  public
     * @var     Closure
     */
    public $afterLogout = null;

    /**
     * Sets the Cloudlib core class and the login route
     *
     * @access  public
     * @param   object  $app        The Cloudlib core class
     * @param   string  $loginRoute The login route
     * @return  void
     */
    public function __construct(Cloudlib $app = null, $loginRoute = null)
    {
        $this->app = $app;
        $this->loginRoute = $loginRoute;

        if( ! $this->app->session)
        {
            throw new RuntimeException('No session handler is available');
        }
    }

    /**
     * Logs in a User
     *
     * @access  public
     * @param   array|object    $user       The user object
     * @param   string          $property   The property to be accessed
     * @return  void
     */
    public function loginUser($user, $property = 'id')
    {
        $this->callFilter('beforeLogin');

        if( ! is_array($user) && ! is_object($user))
        {
            throw new RuntimeException('User has to be of type Array or Object');   
        }

        $user = is_array($user) ? (object) $user : $user;

        if(property_exists($user, $property))
        {
            $this->app->session->refresh();
            $this->app->session->set('user_id', sha1($user->$property));

            $this->callFilter('afterLogin');
        }
        else
        {
            throw new RuntimeException(
                sprintf('Property [%s] does not exist for the User object',
                    $property)
            );
        }

    }

    /**
     * Logs out a User
     *
     * @access  public
     * @return  void
     */
    public function logoutUser()
    {
        $this->callFilter('beforeLogout');

        if($this->isActive())
        {
            $this->app->session->destroy();
        }

        $this->callFilter('afterLogout');
    }

    /**
     * Call a filter if it has been set
     *
     * @access  public
     * @param   string  $filter The filter to be called
     * @return  void
     */
    public function callFilter($filter)
    {
        if($this->$filter instanceof Closure)
        {
            $this->$filter();
        }
    }

    /**
     * Check if a User is logged in (active)
     *
     * @access  public
     * @return  boolean Returns true if the user is active
     */
    public function isActive()
    {
        return $this->app->session->has('user_id');
    }

    /**
     * Check if a user is active, if not cancel the current request
     *
     * @access  public
     * @return  void
     */
    public function loginRequired()
    {
        // TODO check amount of login attempts? or some other method

        if( ! $this->isActive())
        {
            if($this->unauthorizedHandler)
            {
                $this->unauthorizedHandler();
            }

            if($this->loginRoute)
            {
                $this->app->redirect($this->loginRoute, 302);
                // array('next' => $this->app->request->uri)
            }

            $this->app->abort(401);
        }
    }

    /**
     * Set the login route
     *
     * @access  public
     * @param   string  $route  The login route
     * @return  void
     */
    public function loginRoute($route)
    {
        $this->loginRoute = $route;
    }

    /**
     * Set the unauthorized handler
     *
     * @access  public
     * @param   Closure $callback   The callback to be invoked
     * @return  void
     */
    public function unauthorizedHandler(Closure $callback)
    {
        $this->unauthorizedHandler = $callback;
    }
}
