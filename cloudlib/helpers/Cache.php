<?php
/**
 * Cloudlib
 *
 * @author      Sebastian Book <cloudlibframework@gmail.com>
 * @copyright   Copyright (c) 2013 Sebastian Bengtegård <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace cloudlib\helpers;

use RuntimeException;
use DirectoryIterator;

/**
 * The Cache class
 *
 * @copyright   Copyright (c) 2013 Sebastian Bengtegård <cloudlibframework@gmail.com>
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Cache
{
    /**
     * The cache directory
     *
     * @access  public
     * @var     string
     */
    public $directory = null;

    /**
     * The cache file extension
     *
     * @access  public
     * @var     string
     */
    public $extension = '.html';

    /**
     * The cache file expiration timer (defaults to 1 day)
     *
     * @access  public
     * @var     int
     */
    public $expiration = 86400;

    /**
     * Set the cache directory
     *
     * @access  public
     * @param   string  $directory  The cache directory
     * @return  void
     */
    public function __construct($directory)
    {
        $directory = rtrim($directory, '/');

        if( ! file_exists($directory))
        {
            throw new RuntimeException(
                sprintf('Cache directory [%s] does not exist', $directory));
        }

        $this->directory = $directory;
    }

    /**
     * Set something to be cached
     *
     * @access  public
     * @param   string  $key    The cache identifier
     * @param   string  $value  The value to be cached
     * @retun   void
     */
    public function set($key, $value)
    {
        $filename = $this->getFilename($key);

        try
        {
            file_put_contents($filename, $value, LOCK_EX);
        }
        catch(Exception $e)
        {
            throw new RuntimeException(
                sprintf('Unable to write to cache file [%s]', $key));
        }
    }

    /**
     * Get something from the cache
     *
     * @access  public
     * @param   string      $key    The cache identifier
     * @return  str|bool            Returns false if nothing was found else the cached contents
     */
    public function get($key)
    {
        $filename = $this->getFilename($key);

        if( ! file_exists($filename))
        {
            return false;
        }

        if( filemtime($filename) < (time() - $this->expiration))
        {
            $this->delete($key);
            return false;
        }

        return file_get_contents($filename);
    }

    /**
     * Delete a cached item
     *
     * @access  public
     * @param   string  $key    The cache identifier
     * @return  void
     */
    public function delete($key)
    {
        $filename = $this->getFilename($key);
        
        if(file_exists($filename))
        {
            unlink($filename);
        }
    }

    /**
     * Check if a cached item exists
     *
     * @access  public
     * @param   string  $key    The cache identifier
     * @return  bool            Return true if the item exists else false
     */
    public function has($key)
    {
        $filename = $this->getFilename($key);

        if( ! file_exists($filename))
        {
            return false;
        }

        if( filemtime($filename) < (time() - $this->expiration))
        {
            return false;
        }

        return true;
    }

    /**
     * Clear the cache directory of files
     *
     * @access  public
     * @return  void
     */
    public function clear()
    {
        $directory = new DirectoryIterator($this->directory);

        foreach($directory as $fileinfo)
        {
            if($fileinfo->isFile())
            {
                unlink($fileinfo->getFilename());
            }
        }
    }

    /**
     * Get the make time of a cache file
     *
     * @access  public
     * @param   string  $key    Cache identifier
     * @return  int             Returns the file make time
     */
    public function mtime($key)
    {
        $filename = $this->getFilename($key);

        if( ! file_exists($filename))
        {
            return false;
        }

        return filemtime($filename);
    }

    /**
     * Generate a cache filename based off of $key
     *
     * @access  public
     * @param   string  $key    The cache identifier
     * @return  string          The generated name
     */
    public function getFilename($key)
    {
        return sprintf('%s/%s%s', $this->directory, md5($key), $this->extension);
    }

    /**
     * Set the cache directory
     *
     * @access  public
     * @param   string  $directory  The cache directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Set the cache file extension
     *
     * @access  public
     * @param   string  $extension  The cache file extension
     * @return  void
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Set the cache expiration timer
     *
     * @access  public
     * @param   int     $expiration The expiration timer, in seconds
     * @return  void
     */
    public function setExpiration($expiration) 
    {
        $this->expiration = $expiration;
    }

    // TODO: start caching
    public function start() {}
    // TODO: stop caching
    public function stop() {}
}
