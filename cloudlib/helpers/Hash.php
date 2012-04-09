<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use InvalidArgumentException;

/**
 * The Hash class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Hash
{
    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    public function __construct() {}

    /**
     * Create a bcrypt hash
     *
     * @acess   public
     * @param   string  $value  The value to be hashed
     * @param   string  $salt   The hash salt
     * @param   string  $secret The hash secret (static salt)
     * @param   int     $rounds Number of rounds
     * @return  string          Returns a hash
     */
    public static function create($value, $salt, $secret, $rounds = 12)
    {
        if($rounds > 31 || $rounds < 4)
        {
            throw new InvalidArgumentException('The number of rounds has to be between 4-31');
        }

        $salt = sprintf('$2a$%02d$%s', $rounds, substr(base64_encode(sha1($salt . $secret)), 0, 22));

        return substr(crypt($value, $salt), 7);
    }

    /**
     * Compare a newly created hash with an existing hash
     *
     * @access  public
     * @param   string  $hash   The existing hash
     * @param   string  $value  The value to be hashed
     * @param   string  $salt   The hash salt
     * @param   string  $secret The hash secret (static salt)
     * @param   int     $rounds Number of rounds
     * @return  boolean         Returns true if the comparison is equal
     */
    public static function compare($hash, $value, $salt, $secret, $rounds = 12)
    {
        return (bool) (($new = static::create($value, $salt, $secret, $rounds)) == $hash);
    }
}
