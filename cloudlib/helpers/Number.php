<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib;

use InvalidArgumentException;

/**
 * The Number class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Number
{
    /**
     * Array of byte units
     *
     * @access  protected
     * @var     array
     */
    protected static $byteUnits = array(
        'B'  => 1,
        'KB' => 10,
        'MB' => 20,
        'GB' => 30,
        'TB' => 40
    );

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    public function __construct() {}

    /**
     * Shorthand function for toBytes() and fromBytes()
     *
     * @access  public
     * @param   int|string  $value      The value to be converted (string/int)
     * @param   int         $decimals   Number of decimals
     * @return  mixed                   @see toBytes(), @see fromBytes()
     */
    public static function byte($value, $decimals = 3)
    {
        if(is_string($value))
        {
            return static::toBytes($value);
        }

        if(is_int( (int) $value))
        {
            return static::fromBytes($value, $decimals);
        }

        throw new InvalidArgumentException('Argument 1 has to be of type String or Integer');
    }

    /**
     * Convert a byte string (ex 10MB) to its corresponding byte value
     *
     * @access  public
     * @param   string  $value  The value to be converted
     * @return  int             Returns an integer byte value
     */
    public static function toBytes($value)
    {
        if( ! is_string($value))
        {
            throw new InvalidArgumentException('Argument 1 has to be of type String');
        }

        $value = explode($type = substr($value, -2), $value);

        return $value[0] * pow(2, static::$byteUnits[strtoupper($type)]);
    }

    /**
     * Convert a byte value (ex 1024) to megabytes
     *
     * @access  public
     * @param   int     $value      The value to be converted
     * @param   int     $decimals   Number of decimals
     * @return  string              Returns a converted value in megabytes rounded to $decimals
     */
    public static function fromBytes($value, $decimals = 3)
    {
        if( ! is_int( (int) $value))
        {
            throw new InvalidArgumentException('Argument 1 has to be of type Integer');
        }

        return sprintf('%sMB', round(( (int) $value / 1024 / 1024), $decimals));
    }
}
