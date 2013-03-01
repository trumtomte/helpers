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
use Closure;

/**
 * The UploadManager class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class UploadManager
{
    /**
     * The $_FILES array but flattened
     *
     * @access  public
     * @var     array
     */
    public $__files = array();

    /**
     * Array of the uploaded files
     *
     * @access  public
     * @var     array
     */
    public $files = array();

    /**
     * Array of validators
     *
     * @access  public
     * @var     array
     */
    public $validators = array();

    /**
     * Array of hooks
     *
     * @access  public
     * @var     array
     */
    public $hooks = array();
    
    /**
     * Set the $_FILES array (flattened)
     *
     * @access  public
     * @param   array   $__files    The array of uploaded files
     * @return  void
     */
    public function __construct(array $__files)
    {
        $this->__files = $this->getFiles($__files);
    }

    /**
     * Uploads the files to $directory
     *
     * @access  public
     * @param   string  $directory  The directory path for files
     * @throws RuntimeException     If validator returns false, If upload fails
     * @return  void
     */
    public function uploadTo($directory)
    {
        foreach($this->__files as $fileinformation)
        {
            extract($fileinformation);

            $file = new File($error, $name, $tmp, $directory);

            foreach($this->hooks as $hook)
            {
                $hook($file);
            }

            foreach($this->validators as $validator)
            {
                $message = '';

                $validated = $validator($file, $message);

                if( ! $validated)
                {
                    throw new RuntimeException($message);
                }
            }

            if( ! move_uploaded_file($tmp, $file->getFullPath()))
            {
                throw new RuntimeException(sprintf('Unable to upload [%s]',
                    $file->getFullName()
                ));
            }

            $this->files[] = $file;
        }
    }

    /**
     * Check if the $__files array is empty
     *
     * @access  public
     * @return  boolean Returns true if the $__files array is empty
     */
    public function isEmpty()
    {
        foreach($this->__files as $file)
        {
            if($file['error'] !== UPLOAD_ERR_NO_FILE)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets the flattened $_FILES array containing files with corresponding
     * 'error', 'tmp_name' and 'name' keys
     *
     * @access  public
     * @param   array   $items  Array of uploaded files
     * @return  array           The flattened array
     */
    public function getFiles(array $items)
    {
        $items = $this->flatten($items);

        $files = array();

        foreach($items['error'] as $key => $value)
        {
            $files[] = array(
                'error' => $value,
                'name' => $items['name'][$key],
                'tmp' => $items['tmp_name'][$key]
            );
        }

        return $files;
    }

    /**
     * Flattens the $_FILES array
     *
     * @access  public
     * @param   array   $items  Array of uploaded files
     * @return  array           The flattened array
     */
    public function flatten(array $items)
    {
        $temp = array();

        // Recursively flatten an array
        $f = function($arr, $new = array()) use (&$f)
        {
            if(is_array($arr))
            {
                foreach($arr as $val)
                {
                    if(is_array($val))
                    {
                        $new = $f($val, $new);
                    }
                    else
                    {
                        $new[] = $val;
                    }
                }

                return $new;
            }

            $new[] = $arr;
            return $new;
        };

        foreach($items as $item)
        {
            $temp = array_merge_recursive($temp, $item);
        }

        foreach($temp as $key => $value)
        {
            $temp[$key] = $f($value);
        }

        return $temp;
    }

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
     * Helper method for validating filesizes
     *
     * @access  public
     * @param   int $maxsize    The max allowed size in bytes
     * @return  boolean         Returns false if the file exceeds the size
     */
    public function validateFilesize($maxsize)
    {
        $this->addValidator(function($file, &$message) use ($maxsize)
        {
            if($file->size > $maxsize)
            {
                $message = sprintf('File [%s] exceeds the max allowed filesize',
                    $file->getFullName()
                );

                return false;
            }

            return true;
        });
    }

    /**
     * Helper method for validating file extensions
     * 
     * @access  public
     * @param   array   $extensions Array of allowed extensions
     * @return  boolean             Returns false if the file extension is not allowed
     */
    public function validateExtension(array $extensions)
    {
        $this->addValidator(function($file, &$message) use ($extensions)
        {
            if( ! in_array($file->extension, $extensions))
            {
                $message = sprintf('Extension for file [%s] is not allowed',
                    $file->getFullName()
                );

                return false;
            }

            return true;
        });
    }
}

/**
 * The File class
 *
 * @copyright   Copyright (c) 2012 Sebastian Book <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class File extends SplFileInfo
{
    /**
     * The file name (without extension)
     *
     * @access  public
     * @var     string
     */
    public $name = null;

    /**
     * The file extension
     *
     * @access  public
     * @var     string
     */
    public $extension = null;

    /**
     * The file directory path
     *
     * @access  public
     * @var     string
     */
    public $directory = null;

    /**
     * The filesize in bytes
     *
     * @access  public
     * @var     int
     */
    public $size = 0;

    /**
     * The file error code
     *
     * @access  public
     * @var     int
     */
    public $code = null;

    /**
     * Array of available file errors
     *
     * @access  public
     * @var     array
     */
    public $errors = array(
        UPLOAD_ERR_INI_SIZE => 'Exceeds the max filesize (upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE => 'Exceeds the max filesize (max_file_size)',
        UPLOAD_ERR_PARTIAL => 'Was only Partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    );

    /**
     * Sets the file error code, name, temp path and directory path
     *
     * @access  public
     * @param   int     $code   The error code
     * @param   string  $name   The file name
     * @param   string  $tmp    The temp path
     * @param   string  $dir    The directory path
     * @throws  RuntimeException    If the file is invalid
     * @return  void
     */
    public function __construct($code, $name = null, $tmp = null, $dir = null)
    {
        if($code !== UPLOAD_ERR_OK)
        {
            throw new RuntimeException($this->errors[$code]);
        }

        $this->code = $code;
        $this->name = pathinfo($name, PATHINFO_FILENAME);
        $this->extension = pathinfo($name, PATHINFO_EXTENSION);
        $this->directory = rtrim($dir, '/') . '/';

        parent::__construct($tmp);

        if( ! $this->isFile())
        {
            throw new RuntimeException(sprintf('File [%s] is not valid', $name));
        }

        $this->size = $this->getSize();
    }

    /**
     * Gets the filename + extension
     *
     * @access  public
     * @return  string  The full filename
     */
    public function getFullName()
    {
        return sprintf('%s.%s', $this->name, $this->extension);
    }

    /**
     * Gets the full filename + directory path
     *
     * @access  public
     * @return  string  The full file path
     */
    public function getFullPath()
    {
        return sprintf('%s%s.%s', $this->directory, $this->name, $this->extension);
    }

    /**
     * Sets the prefix for the file
     *
     * @access  public
     * @param   string  $prefix The file prefix
     * @return  void
     */
    public function setPrefix($prefix)
    {
        $this->name = $prefix . $this->name;
    }

    /**
     * Sets the suffix for the file
     *
     * @access  public
     * @param   string  $suffix The file suffix
     * @return  void
     */
    public function setSuffix($suffix)
    {
        $this->name .= $suffix;
    }

    /**
     * Gets the human readable file size (ex. 10MB, 230KB)
     *
     * @access  public
     * @param   int $decimals   Amount of decimals to be rounded to
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
