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
 * The String class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class String
{
    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    public function __construct() {}

    /**
     * Repeat a string a number of times with the option for a string separator
     *
     * @access  public
     * @param   string  $string     The string to be repeated
     * @param   int     $times      Number of times to be repeated
     * @param   string  $separator  The string separator
     * @return  string              Returns the new, repeated, string
     */
    public static function repeat($string, $times = 2, $separator = null)
    {
        return (separator) ? str_repeat($string . $separator, ($times - 1)) . $string : str_repeat($string, $times);
    }

    /**
     * Shorthand function for mb_strimwidth()
     *
     * @access  public
     * @param   string  $string     The string to be trimmed
     * @param   int     $width      The width of the string (length)
     * @param   int     $start      Where to start the trimmin'
     * @param   string  $marker     What to replace at the end of the string
     * @return  string              Returns a new trimmed string
     */
    public static function trim($string, $width, $start = 0, $marker = '...')
    {
        return mb_strimwidth($string, $start, $width, $marker);
    }

    // TODO: more mb_<function>'s
}
