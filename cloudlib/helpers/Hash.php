<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use InvalidArgumentException;

/**
 * The Hash class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Hash
{
    /**
     * Default secret to be used with hashing
     *
     * @access  public
     * @var     string
     */
    public $secret = null;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    public function __construct($secret = null)
    {
        $this->secret = $secret;
    }

    /**
     * Create a bcrypt hash
     *
     * @acess   public
     * @param   string  $value  The value to be hashed
     * @param   string  $salt   The hash salt
     * @param   int     $cost   Cost of the hash
     * @throws InvalidArgumentException If the cost is not between 4 and 31
     * @return  string          Returns the hash string
     */
    public function generate($value, $salt, $cost = 12)
    {
        if($cost > 31 || $cost < 4)
        {
            throw new InvalidArgumentException('The cost has to be between 4 and 31');
        }

        $salt = sprintf('$2a$%02d$%s', $cost,
            substr(base64_encode(sha1($salt . $this->secret)), 0, 22));

        return substr(crypt($value, $salt), 7);
    }

    /**
     * Compare a newly created hash with an existing hash
     *
     * @access  public
     * @param   string  $hash   The existing hash
     * @param   string  $value  The value to be hashed
     * @param   string  $salt   The hash salt
     * @param   int     $cost   Cost of the hash
     * @return  boolean         Returns true if it is the same hash
     */
    public function compare($hash, $value, $salt, $cost = 12)
    {
        $input = $this->generate($value, $salt, $cost);
    
        return (bool) ($input == $hash);
    }
}
