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
 * The Benchmark Class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Benchmark
{
    /**
     * Associative array of start and stop times
     *
     * @access  public
     * @var     array
     */
    public static $times = array();

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    public function __construct() {}

    /**
     * Define a start time
     *
     * @access  public
     * @param   string  $name   Identifier for the start time
     * @return  void
     */
    public static function start($name)
    {
        static::$times[$name]['start'] = microtime(true);
    }

    /**
     * Define a stop time
     *
     * @access  protected
     * @param   string  $name   Identifier for the stop time
     * @return  void
     */
    protected static function stop($name)
    {
        static::$times[$name]['stop'] = microtime(true);
    }

    /**
     * Get the elapsed time since the start
     *
     * @access  public
     * @param   string  $name       Identifier for the start time
     * @param   int     $decimals   Number of decimals
     * @return  float               Returns the elapsed time rounded to $decimals
     */
    public static function get($name, $decimals = 5)
    {
        static::stop($name);

        return (float) round((static::$times[$name]['stop'] - static::$times[$name]['start']), $decimals);
    }

    /**
     * Get the elapsed time difference from a defined timestamp
     *
     * @access  public
     * @param   float   $time       The defined timestamp
     * @param   int     $decimals   Number of decimals
     * @return  float               Returns the elapsed time difference rounded to $decimals
     */
    public static function compare($time, $decimals = 5)
    {
        return (float) round((microtime(true) - (float) $time), $decimals);
    }

    /**
     * Gets the current memory usage in Megabytes rounded to three decimals
     *
     * @access  public
     * @param   int     $decimals   Number of decimals
     * @return  float               Returns the memory usage in megabytes rounded to $decimals
     */
    public static function memory($decimals = 3)
    {
        return (float) round((memory_get_usage() / 1024 / 1024), $decimals);
    }

    /**
     * Gets the peak memory usage in Megabytes rounded to three decimals
     *
     * @access  public
     * @param   int     $decimals   Number of decimals
     * @return  float               Returns the peak memory usage in megabytes rounded to $decimals
     */
    public static function peak($decimals = 3)
    {
        return (float) round((memory_get_peak_usage() / 1024 / 1024), $decimals);
    }

    /**
     * Shorthand function for Benchmark::get();
     *
     * ex. Benchmark::boot_time(5); // Is equivalent to Benchmark::get('boot_time', 5);
     *
     * @access  public
     * @param   string  $name   Identifier for the start time
     * @param   array   $args   Array of arguments, in this case the parameter "Decimals"
     * @return  float           Returns the elapsed time rounded to $deciamls
     */
    public static function __callStatic($name, array $args)
    {
        $decimals = isset($args[0]) ? $args[0] : 5;

        return static::time($time, $decimals);
    }
}
