<?php
/**
 * Cloulib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

/**
 * The HTML Class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Html
{
    /**
     * Relative path to css files
     *
     * @access  public
     * @var     string
     */
    public static $css = null;

    /**
     * Relative path to js files
     *
     * @access  public
     * @var     string
     */
    public static $js = null;

    /**
     * Relative path for anchor links
     *
     * @access  public
     * @var     string
     */
    public static $a = null;

    /**
     * Relative path for images
     *
     * @access  public
     * @var     string
     */
    public static $img = null;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    public function __construct() {}

    /**
     * Create a string of css <link>'s
     *
     * @access  public
     * @param   string|array  $filename Stylesheet filename
     * @return  string                  Returns a string of css <link>'s
     */
    public static function css($filename)
    {
        if(is_string($filename))
        {
            return sprintf('<link rel="stylesheet" href="%s">' . PHP_EOL,
                sprintf('%s%s.css', static::$css, $filename));
        }

        if(is_array($filename))
        {
            $stylesheets = null;

            foreach($filename as $value)
            {
                $stylesheets .= sprintf('<link rel="stylesheet" href="%s">%s', PHP_EOL,
                    sprintf('%s%s.css', static::$css, $filename));
            }

            return $stylesheets;
        }
    }

    /**
     * Create a string of javascript <script> links
     *
     * @access  public
     * @param   string|array  $filename JavaScript filename
     * @return  string                  Returns a string of javascript <script> links
     */
    public static function js($filename)
    {
        if(is_string($filename))
        {
            return sprintf('<script src="%s"></script>' . PHP_EOL,
                sprintf('%s%s.js', static::$js, $filename));
        }

        if(is_array($filename))
        {
            $scripts = null;

            foreach($filename as $value)
            {
                $scripts .= sprintf('<script src="%s"></script>' . PHP_EOL,
                    sprintf('%s%s.js', static::$js, $value));
            }

            return $scripts;
        }
    }

    /**
     * Create an anchor (<a>) element
     *
     * @access  public
     * @param   string  $path       The anchor path (href)
     * @param   string  $content    The anchor content
     * @param   array   $attributes Array of anchor attribute:value pairs, with the exception for "relative"
     * @return  string              Returns an anchor element
     */
    public static function a($path, $content, array $attributes = array())
    {
        if(isset($attributes['relative']))
        {
            $relpath = null;
            unset($attributes['relative']);
        }
        else
        {
            $relpath = static::$a;
        }

        return sprintf('<a href="%s%s" %s>%s</a>', $relpath, $path,
            static::getAttrStr($attributes), $content);
    }

    /**
     * Create an image (<img>) element
     *
     * @access  public
     * @param   string  $path       The image source path (src)
     * @param   array   $attributes Array of image attribute:value pairs, with the exception of "relative"
     * @return  string              Returns an image element
     */
    public static function img($path, array $attributes = array())
    {
        if(isset($attributes['relative']))
        {
            $relpath = null;
            unset($attributes['relative']);
        }
        else
        {
            $relpath = static::$img;
        }

        return sprintf('<img src="%s%s" %s>', $relpath, $path, static::getAttrStr($attributes));
    }

    /**
     * Create a <script> code block
     *
     * @access  public
     * @param   string  $script     The JavaScript code
     * @return  string              Return a <script> code block string
     */
    public static function script($script)
    {
        return sprintf('<script>%s%s%s</script>' . PHP_EOL, PHP_EOL, $script, PHP_EOL);
    }

    /**
     * Create a <style> code block
     *
     * @access  public
     * @param   string  $style  The CSS code
     * @return  string          Return a <style> code block string
     */
    public static function style($style)
    {
        return sprintf('<style>%s%s%s</style>' . PHP_EOL, PHP_EOL, $style, PHP_EOL);
    }

    /**
     * Return <br> a number of times
     *
     * @access  public
     * @param   int     $times  Number of times to be repeated
     * @return  string          Returns a string of <br>'s
     */
    public static function br($times = 1)
    {
        return str_repeat('<br>', $times) . PHP_EOL;
    }

    /**
     * Take an array of attributes and return it as a string
     *
     * @access  public
     * @param   array   $attributes Array of attribute:value pairs
     * @return  string              Return a string of attribute="value" pairs
     */
    public static function getAttrStr(array $attributes)
    {
        $string = null;

        foreach($attributes as $key => $value)
        {
            $string .= sprintf('%s="%s" ', $key, $value);
        }

        return $string;
    }
}
