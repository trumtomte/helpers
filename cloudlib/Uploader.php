<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib;

use SplFileInfo;

/**
 * The Uploader class
 *
 * @copyright   Copyright (c) 2011 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Uploader
{
    /**
     * Array of configuration items
     *
     * @access  public
     * @var     array
     */
    public $config = array(
        // Directory path to be prepended to filenames
        'directory' => '',
        // Array of allowed filetypes (extensions)
        'filetypes' => null,
        // Max allowed filesize (default: 2MB)
        'filesize' => 2097152,
        // If set, this will be the filename
        'filename' => null,
        // String to be prepended to filenames
        'prefix' => null,
        // Max allowed Width of an image
        'width' => null,
        // Max allowed Height of an image
        'height' => null,
        // If we should replace the existing file or not, if not copy_(num) will be prepended
        'replace' => false
    );

    /**
     * PHP File errors
     *
     * @access  protected
     * @var     array
     */
    protected $fileErrors = array(
        1 => 'Exceeds the max filesize',
        2 => 'Exceeds the max filesize',
        3 => 'Was only Partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload'
    );

    /**
     * Array of image extensions recognized by the class
     *
     * @access  protected
     * @var     array
     */
    protected $imageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

    /**
     * The $_FILES array
     *
     * @access  protected
     * @var     array
     */
    protected $files = array();

    /**
     * Array that will be set if error occurs
     *
     * @access  protected
     * @var     array
     */
    protected $errors = null;

    /**
     * Array of information about the uploaded files
     *
     * @access  protected
     * @var     array
     */
    protected $data = array();

    /**
     * Constructor, set the array of files ($_FILES) and set (optional) the config array
     *
     * @access  public
     * @param   array   $files      Array of files ($_FILES)
     * @param   array   $config     Array of configuration items
     * @return  void
     */
    public function __construct(array $files, array $config = array())
    {
        $this->files = array_shift($files);

        if(isset($config))
        {
            $this->setConfig($config);
        }
    }

    /**
     * Check if any files has been uploaded
     *
     * @access  public
     * @return  boolean Return true if no files has been uploaded, else false
     */
    public function isEmpty()
    {
        if(is_array($this->files['error']))
        {
            return (bool) in_array(4, $this->files['error']);
        }

        return (bool) ($this->files['error'] !== 4);
    }

    /**
     * Initialize the uploading process
     *
     * @access  public
     * @return  boolean Return true if the uploading process was successful, else false and set the error accordingly
     */
    public function upload()
    {
        if(isset($this->files))
        {
            if(is_array($this->files['name']))
            {
                return $this->uploadMultiple();
            }

            return $this->uploadSingle();
        }

        $this->errors[] = 'No File(s) was uploaded';

        return false;
    }

    /**
     * Upload a single file
     *
     * @access  protected
     * @return  boolean     Returns true if the uploading process was successful
     */
    protected function uploadSingle()
    {
        $this->process($this->files['name'], $this->files['tmp_name'],
            $this->files['error'], 0);

        if($this->errors === null)
        {
            return true;
        }

        return false;
    }

    /**
     * Upload multiple files
     *
     * @access  protected
     * @return  boolean     Returns true if the uploading process was successful
     */
    protected function uploadMultiple()
    {
        foreach($this->files['name'] as $key => $value)
        {
            $this->process($this->files['name'][$key], $this->files['tmp_name'][$key],
                $this->files['error'][$key], $key);
        }

        if($this->errors === null)
        {
            return true;
        }

        return false;
    }

    /**
     * Perform the uploading process
     *
     * @access  protected
     * @param   string  $name   The filename
     * @param   string  $tmp    The temporary filename
     * @param   int     $error  File (php) error
     * @param   int     $key    File index
     * @return  boolean         Return true if the process was successful, else false and set the error accordingly
     */
    protected function process($name, $tmp, $error, $key)
    {
        if($error !== 0)
        {
            $this->errors[$key] = array('error' => $this->fileErrors[$error]);
            return false;
        }

        $nameObj = new SplFileInfo($name);
        $tempObj = new SplFileInfo($tmp);

        // Is it a file?
        if($tempObj->isFile() == false)
        {
            $this->error[$key] = array('name' => $name, 'error' => 'File is invalid');
            return false;
        }

        $this->data[$key]['origname'] = $name;

        // Valid filesize?
        if($tempObj->getSize() > static::$config['filesize'])
        {
            $this->errors[$key] = array(
                'name' => $name,
                'error' => 'File exceeds the max allowed filesize'
            );
            return false;
        }

        $this->data[$key]['size'] = $tempObj->getSize();

        // Valid file extension?
        if(isset(static::$config['filetypes']) && is_array(static::$config['filetypes']))
        {
            if( ! in_array($nameObj->getExtension(), static::$config['filetypes']))
            {
                $this->error[$key] = array(
                    'name' => $name,
                    'error' => 'Invalid file extension'
                );
                return false;
            }
        }

        $this->data[$key]['ext'] = $nameObj->getExtension();

        // Is the file an image?
        if(in_array($nameObj->getExtension(), $this->imageExtensions))
        {
            list($width, $height) = getimagesize($tmp);

            // Check width
            if(isset(static::$config['width']))
            {
                if($width > static::$config['width'])
                {
                    $this->error[$key] = array(
                        'name' => $name,
                        'error' => 'Invalid width'
                    );
                    return false;
                }
            }

            // Check height
            if(isset(static::$config['height']))
            {
                if($height > static::$config['height'])
                {
                    $this->error[$key] = array(
                        'name' => $name,
                        'error' => 'Invalid height'
                    );
                    return false;
                }
            }

            $this->data[$key]['image'] = 1;
            $this->data[$key]['width'] = $width;
            $this->data[$key]['height'] = $height;
        }
        else
        {
            $this->data[$key]['image'] = 0;
            $this->data[$key]['width'] = 0;
            $this->data[$key]['height'] = 0;
        }

        // Set name
        $prefix = isset(static::$config['prefix']) ? static::$config['prefix'] : '';

        if(isset(static::$config['filename']))
        {
            $newName = sprintf('%s%s.%s', $prefix, static::$config['filename'],
                $nameObj->getExtension());
        }
        else
        {
            $newName = $prefix . $name;
        }

        $newName = str_replace(' ', '_', $newName);

        // If we should replace the existing file, if not - check it it exists and
        // prepend the filename with copy_(number)
        if(static::$config['replace'] == false)
        {
            $copy = '';
            $counter = 1;

            while(file_exists(static::$config['directory'] . $copy . $newName))
            {
                $copy = sprintf('copy(%s)_', $counter);
                $counter++;
            }

            $newName = $copy . $newName;
        }

        $this->data[$key]['name'] = $newName;

        $dir = rtrim(static::$config['directory'], '/') . '/';

        if( ! move_uploaded_file($tmp, $dir . $newName))
        {
            $this->error[$key] = array(
                'name' => $name,
                'error' => 'Unable to upload the chosen file'
            );
            return false;
        }

        $this->data[$key]['path'] = $dir;
        $this->data[$key]['fullpath'] = $dir . $newName;

        return true;
    }

    /**
     * Get the error array
     *
     * @access  public
     * @return  array   Returns an array of defined errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the data array
     *
     * @access  public
     * @return  array   Returns an array of set information about the uploaded files
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the config array
     *
     * @access  public
     * @param   array   $config     Array of configuration items to be set
     * @return  void
     */
    public function setConfig(array $config)
    {
        foreach($config as $key => $value)
        {
            $this->config[$key] = $value;
        }
    }
}
