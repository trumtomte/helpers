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

/**
 * The Image class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Image
{
    /**
     * The image resource
     *
     * @access  protected
     * @var     string
     */
    protected $image = null;

    /**
     * Directory path to be prepended to filenames
     *
     * @access  public
     * @var     string
     */
    public $directory = null;

    /**
     * Current image extension
     *
     * @access  protected
     * @var     string
     */
    protected $extension;

    /**
     * Current image width
     *
     * @access  protected
     * @var     int
     */
    protected $width;

    /**
     * Current image height
     *
     * @access  protected
     * @var     int
     */
    protected $height;

    /**
     * JPEG compression value
     *
     * @access  public
     * @var     int
     */
    public $compression = 75;

    /**
     * The error
     *
     * @access  protected
     * @var     string
     */
    protected $error;

    /**
     * Allowed image extensions
     *
     * @access  protected
     * @var     array
     */
    protected $imageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

    /**
     * Constructor.
     *
     * Load the image (optional) and set the jpeg compression value (optional)
     *
     * @access  public
     * @param   string  $filename       Filename to be loaded
     * @param   int     $compression    JPEG compression value
     * @return  void
     */
    public function __construct($filename = null, $compression = null)
    {
        if($filename !== null)
        {
            $this->load($filename);
        }

        if($compression !== null)
        {
            $this->compression = $compression;
        }
    }

    /**
     * Load an image
     *
     * @access  public
     * @param   string  $filename   Filename to be loaded
     * @return  void
     */
    public function load($filename)
    {
        $file = $this->directory . $filename;

        $image = new SplFileInfo($file);

        if( ! in_array($image->getExtension(), $this->imageExtensions))
        {
            $this->error = 'Invalid file extension';
            return false;
        }

        $this->extension = $image->getExtension();

        list($this->width, $this->height) = getimagesize($file);

        switch($image->getExtension())
        {
            case 'jpg':
            case 'jpeg':
                $this->image = imagecreatefromjpeg($file);
                break;
            case 'png':
                $this->image = imagecreatefrompng($file);
                break;
            case 'gif':
                $this->image = imagecreatefromgif($file);
                break;
            case 'bmp':
                $this->image = imagecreatefromwbmp($file);
                break;
            default:
                $this->error = 'Unable to create image (unknown extension)';
                break;
        }
    }

    /**
     * Save an image
     *
     * @access  public
     * @param   string  $filename   Filename to be saved to
     * @return  void
     */
    public function save($filename)
    {
        $file = $this->directory . $filename;

        switch($this->extension)
        {
            case 'jpg':
            case 'jpeg':
                imagejpeg($this->image, $file, $this->compression);
                break;
            case 'png':
                imagepng($this->image, $file);
                break;
            case 'gif':
                imagegif($this->image, $file);
                break;
            case 'bmp':
                imagewbmp($this->image, $file);
                break;
            default:
                $this->error = 'Unable to write image (unknown extension)';
                break;
        }

        if(is_resource($this->image))
        {
            imagedestroy($this->image);
        }
    }

    /**
     * Resize an image to the given width
     *
     * @access  public
     * @param   int     $width  The width
     * @return  void
     */
    public function resizeToWidth($width)
    {
        if($this->image === null)
        {
            $this->error = 'No image has been loaded';
            return false;
        }

        $height = ($width / $this->width) * $this->height;

        $this->resample($width, $height);
    }

    /**
     * Resize an image to the given height
     *
     * @access  public
     * @param   int     $height     The height
     * @return  void
     */
    public function resizeToHeight($height)
    {
        if($this->image === null)
        {
            $this->error = 'No image has been loaded';
            return false;
        }

        $width = ($height / $this->height) * $this->width;

        $this->resample($width, $height);
    }

    /**
     * Scale an image by a certain percentage
     *
     * @access  public
     * @param   int     $scale  The scale (percentage)
     * @return  void
     */
    public function scale($scale)
    {
        if($this->image === null)
        {
            $this->error = 'No image has been loaded';
            return false;
        }

        $width = ($scale / 100) * $this->width;
        $height = ($scale / 100) * $this->height;

        $this->resample($width, $height);
    }

    // TODO: shorthand function to create thumbnails
    public function thumb() {}

    /**
     * Shorthand function for cropping an image from the center
     *
     * @access  public
     * @param   int     $width      The width
     * @param   int     $height     The height
     * @return  void
     */
    public function cropCenter($width = 100, $height = 100)
    {
        if($this->image === null)
        {
            $this->error = 'No image has been loaded';
            return false;
        }

        $max = max($this->width, $this->height);

        $srcW = $max * 0.5;
        $srcH = $max * 0.5;

        $x = ($this->width - $srcW) / 2;
        $y = ($this->height - $srcH) / 2;

        $this->resample($width, $height, $srcW, $srcH, $x, $y);
    }

    // TODO: function that will crop an image by x, y, width and height
    public function crop() {}

    /**
     * Resample an image
     *
     * @access  protected
     * @param   int     $destW  Destination width
     * @param   int     $destH  Destination height
     * @param   int     $srcW   Source width
     * @param   int     $srcH   Source height
     * @param   int     $x      Horizontal position
     * @param   int     $y      Vertical position
     * @return  void
     */
    protected function resample($destW, $destH, $srcW = null, $srcH = null, $x = 0,
        $y = 0)
    {
        $srcW = ($srcW === null) ? $this->width : $srcW;
        $srcH = ($srcH === null) ? $this->height : $srcH;

        if( ! ($identifier = imagecreatetruecolor($destW, $destH)))
        {
            $this->error = 'Resampling failed, imagecreatetruecolor() returned false';
            return false;
        }

        if( ! imagecopyresampled($identifier, $this->image, 0, 0, $x, $y, $destW, $destH,
            $srcW, $srcH))
        {
            $this->error = 'Resampling failed, imagecopyresampled() returned false';
            return false;
        }

        $this->image = $identifier;
    }

    /**
     * Set the JPEG compression value
     *
     * @access  public
     * @param   int     $compression
     * @return  void
     */
    public function setCompression($compression)
    {
        $this->compression = (int) $compression;
    }

    /**
     * Define the directory path to be prepended to filenames
     *
     * @access  public
     * @param   string  $directory  The directory path
     * @return  void
     */
    public function directory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Get the error
     *
     * @access  public
     * @return  string  Returns the defined error
     */
    public function getError()
    {
        return $this->error;
    }
}
