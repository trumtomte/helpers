<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use SplFileInfo;
use RuntimeException;
use Exception;

/**
 * The ImageHandler class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class ImageHandler
{
    /**
     * Array of allowed image extensions
     *
     * @access  public
     * @var     array
     */
    public $extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

    /**
     * The image Object
     *
     * @access  public
     * @var     object
     */
    public $image = null;
    
    /**
     * The image Resource
     *
     * @access  public
     * @var     resource
     */
    public $resource = null;

    /**
     * Array of validators to be run on ImageHandler::saveTo()
     *
     * @access  public
     * @var     array
     */
    public $validators = array();

    /**
     * Array of hooks to be run on ImageHandler::saveTo()
     *
     * @access  public
     * @var     array
     */
    public $hooks = array();

    /**
     * Add a validator
     *
     * @access  public
     * @param   Closure $callback   The validator
     * @return  void
     */
    public function addValidator(Closure $callback)
    {
        $this->validators[] = $callback;
    }

    /**
     * Add a hook
     *
     * @access  public
     * @param   Closure $callback   The hook
     * @return  void
     */
    public function addHook(Closure $callback)
    {
        $this->hooks[] = $callback;
    }

    /**
     * Load an image, create the object and define the resource
     * 
     * @access  public
     * @param   string  $name   The file path for the image
     * @return  void
     */
    public function load($name)
    {
        $this->image = new Image($name);

        if( ! in_array($this->image->extension, $this->extensions))
        {
            throw new RuntimeException(sprintf('Image [%s] extension invalid',
                $image->getFullName()
            ));
        }

        switch($this->image->extension)
        {
            case 'jpg':
            case 'jpeg':
            default:
                $this->resource = imagecreatefromjpeg($name);
                break;
            case 'png':
                $this->resource = imagecreatefrompng($name);
                break;
            case 'gif':
                $this->resource = imagecreatefromgif($name);
                break;
            case 'bmp':
                $this->resource = imagecreatefromwbmp($name);
                break;
        }

    }

    /**
     * Save an image to $path, run available hooks and validators
     *
     * @access  public
     * @param   string  $path           The file path to be saved to
     * @param   int     $compression    The jpeg quality
     * @throws  RuntimeException    If validation fails
     * @return  void
     */
    public function saveTo($path, $compression = 75)
    {
        foreach($this->hooks as $hook)
        {
            $hook($this->resource, $this->image);
        }

        foreach($this->validators as $validator)
        {
            $message = 'Image validation failed';

            $validated = $validator($this->resource, $this->image, $message);

            if( ! $validated)
            {
                throw new RuntimeException($message);
            }
        }

        switch($this->image->extension)
        {
            case 'jpg':
            case 'jpeg':
            default:
                imagejpeg($this->resource, $path, $compression);
                break;
            case 'png':
                imagepng($this->resource, $path);
                break;
            case 'gif':
                imagegif($this->resource, $path);
                break;
            case 'bmp':
                imagewbmp($this->resource, $path);
                break;
        }

        if(is_resource($this->resource))
        {
            imagedestroy($this->resource);
        }
    }

    /**
     * Rotate an image by $degrees
     *
     * @access  public
     * @param   int $degrees    The amount of degrees to be rotated by
     * @return  void
     */
    public function rotate($degrees)
    {
        $this->resource = imagerotate($this->resource, (int) $degrees, 0);
        
        if(in_array($this->image->extension, array('png', 'gif')))
        {
            imagecolortransparent($this->resource,
                imagecolorallocatealpha($this->resource, 0, 0, 0, 127));
            imagealphablending($this->resource, false);
            imagesavealpha($this->resource, true);
        }
    }

    /**
     * Crop an image at destinations $x and $y to $width and $height
     *
     * @access  public
     * @param   int $width  Image width
     * @param   int $height Image height
     * @param   int $x      X position
     * @param   int $y      Y position
     * @throws  RuntimeException    If any GD function fails
     * @return  void
     */
    public function crop($width, $height, $x = 0, $y = 0)
    {
        try
        {
            $resource = imagecreatetruecolor($width, $height);

            imagecopy($resource, $this->resource, 0, 0, $x, $y,
                $this->image->width, $this->image->height);

            $this->resource = $resource;

        }
        catch(Exception $e)
        {
            throw new RuntimeException(sprintf('Failed to crop image [%s]',
                $this->image->getFullPath()
            ));
        }
    }

    /**
     * Crop an image from the center to $width and $height
     *
     * @access  public
     * @param   int $width  Image width
     * @param   int $heigh  Image height
     * @return  void
     */
    public function cropCenter($width, $height)
    {
        $centerX = $this->image->width / 2;
        $centerY = $this->image->height / 2;

        $x = $centerX - $width / 2;
        $y = $centerY - $height / 2;

        $x = ($x < 0) ? 0 : $x;
        $y = ($y < 0) ? 0 : $y;

        $this->crop($width, $height, $x, $y);
    }

    /**
     * Resize an image to $width and $height
     *
     * @access  public
     * @param   int $width  Image width
     * @param   int $height Image height
     * @throws  RuntimeException    If any GD function fails
     * @return  void
     */
    public function resize($width, $height)
    {
        try
        {
            $resource = imagecreatetruecolor($width, $height);

            imagecopyresampled($resource, $this->resource, 0, 0, 0, 0,
                $width, $height, $this->image->width, $this->image->height);

            $this->resource = $resource;
        }
        catch(Exception $e)
        {
            throw new RuntimeException(sprintf('Failed to resize image [%s]',
                $this->image->getFullPath()
            ));
        }
    }

    /**
     * Resize an image based on $width
     *
     * @access  public
     * @param   int $width  Image width
     * @return  void
     */
    public function resizeToWidth($width)
    {
        $height = ($width / $this->image->width) * $this->image->height;
        
        $this->resize($width, $height);
    }

    /**
     * Resize an image based on $height
     *
     * @access  public
     * @param   int $height Image height
     * @return  void
     */
    public function resizeToHeight($height)
    {
        $width = ($height / $this->image->height) * $this->image->width;

        $this->resize($width, $height);
    }

    /**
     * Scale an image by $percentage
     *
     * @access  public
     * @param   int $percentage The percentage of which we scale the image
     * @return  void
     */
    public function scale($percentage)
    {
        $width = ($percentage / 100) * $this->image->width;
        $height = ($percentage / 100) * $this->image->height;

        $this->resize($width, $height);
    }
}

/**
 * The Image class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Image extends SplFileInfo
{
    /**
     * Name of the image (without extension)
     *
     * @access  public
     * @var     string
     */
    public $name = null;

    /**
     * Image extension
     *
     * @access  public
     * @var     string
     */
    public $extension = null;

    /**
     * File path to the image
     *
     * @access  public
     * @var     string
     */
    public $directory = null;

    /**
     * Image mime type
     *
     * @access  public
     * @var     string
     */
    public $mimetype = null;

    /**
     * Image width
     *
     * @access  public
     * @var     int
     */
    public $width = 0;

    /**
     * Image width
     *
     * @access  public
     * @var     int
     */
    public $height = 0;

    /**
     * Image size
     *
     * @access  public
     * @var     int
     */
    public $size = 0;

    /**
     * Extract all information from the image and set the corresponding class properties
     *
     * @access  public
     * @param   string  $image      Image file path
     * @throws  RuntimeException    If the file is not an image or if the file is not valid
     * @return  void
     */
    public function __construct($image)
    {
        $imageinformation = getimagesize($image);

        if( ! $imageinformation)
        {
            throw new RuntimeException(sprintf('File [%s] is not an image',
                $image
            ));
        }

        parent::__construct($image);

        if( ! $this->isFile())
        {
            throw new RuntimeException(sprintf('File [%s] is not valid', $name));
        }

        list($this->width, $this->height, $this->mimetype) = $imageinformation;

        $this->name = pathinfo($image, PATHINFO_FILENAME);
        $this->extension = pathinfo($image, PATHINFO_EXTENSION);
        $this->directory = rtrim(pathinfo($image, PATHINFO_DIRNAME), '/') . '/';
        $this->size = $this->getSize();
    }

    /**
     * Get the full name of the image, name + extension
     *
     * @access  public
     * @return  string  The full file name
     */
    public function getFullName()
    {
        return sprintf('%s.%s', $this->name, $this->extension);
    }

    /**
     * Get the full name with directory of the image
     *
     * @access  public
     * @return  string  The full file name with directory
     */
    public function getFullPath()
    {
        return sprintf('%s%s.%s', $this->directory, $this->name, $this->extension);
    }

    /**
     * Gets an human readable filesize (ex 10MB, 32.3KB etc)
     * 
     * @access  public
     * @param   int $decimals   Number of decimals
     * @return  string          The human readable filesize
     */
    public function getHumanReadableSize($decimals = 1)
    {
        $units = array('B', 'KB', 'MB', 'GB');

        $base = log($this->size) / log(1024);

        $value = round(pow(1024, $base - floor($base)), $decimals);

        return sprintf('%s %s', $value, $units[floor($base)]);
    }
}
