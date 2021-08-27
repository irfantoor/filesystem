<?php

/**
 * IrfanTOOR\Filesystem
 * php version 7.3
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2021 Irfan TOOR
 */

namespace IrfanTOOR;

use DirectoryIterator;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class FileSystem
{
    const NAME        = "Irfan's Filesystem";
    const DESCRIPTION = "Irfan's Filesystem : A single-file minimum filesystem class to manage the files and directories.";
    const VERSION     = "0.4";

    /** @var string Root path */
    protected $root;

    /**
     * Constructs the Filesystem
     *
     * @param string Root path
     */
    function __construct($root)
    {
        $root = str_replace('../', '/', $root);
        $this->root = rtrim($root, '/') . '/';

        if (!is_dir($this->root)) {
            throw new Exception("root dir: {$this->root}, does not exist", 1);
        }
    }

    /**
     * Normalise the path
     *
     * @param string path
     *
     * @return string path
     */
    function normalise($path)
    {
        # remove /../../ etc.
        $path = str_replace('../', '/', $path);

        # remove ./
        $path = str_replace('./', '/', $path);

        # collapse ///... to /
        $path = preg_replace('|\/\/+|Us', '/', $path);

        # remove left / and right /
        $path = ltrim($path, '/');
        $path = rtrim($path, '/');

        # remove the dot files
        if ($path === '.' || $path === '..') {
            $path = '';
        }

        return $path;
    }

    /**
     * @param string relative path of file
     *
     * @return bool true if the filesystem has the file
     */
    private function _has($file)
    {
        return is_file($this->root . $file);
    }

    /**
     * Verifies if the filesystem has the file
     *
     * @param string relative path of file
     *
     * @return bool true if the filesystem has the file
     */
    function has($file)
    {
        $file = $this->normalise($file);
        return $this->_has($file);
    }

    /**
     * Reads and returns the contnets of a file
     *
     * @param string relative path of file
     *
     * @return mixed contents of the file or
     */
    function read($file)
    {
        $file = $this->normalise($file);
        return file_get_contents($this->root . $file);
    }

    /**
     * Writes to a file
     *
     * @param string relative path of file
     * @param string contents to write
     * @param bool   force to write, even if a file already exists
     *
     * @return int size of the contents written to file
     */
    function write($file, $contents, $force = false)
    {
        $file = $this->normalise($file);

        if (!$force && $this->_has($file)) {
            throw new Exception("file: $file, already exists", 1);
        }

        return file_put_contents($this->root . $file, $contents);
    }

    /**
     * Rename a file
     *
     * @param string from
     * @param string to
     *
     * @return bool true if the operation was successful, false otherwise
     */
    function rename($from, $to)
    {
        $from = $this->normalise($from);
        $to = $this->normalise($to);

        if (!$this->_has($from)) {
            throw new Exception("source: $from, does not exist", 1);
        }

        if (!$force && $this->_has($to)) {
            throw new Exception("target: $to, already exists", 1);
        }

        return rename($this->root . $from, $this->root . $to);
    }

    /**
     * Copy a file
     *
     * @param string from
     * @param string to
     *
     * @return bool true if the operation was successful, false otherwise
     */
    function copy($from, $to, $force = false)
    {
        $from = $this->normalise($from);
        $to = $this->normalise($to);

        if (!$this->_has($from)) {
            throw new Exception("source: $from, does not exist", 1);
        }

        if (!$force && $this->_has($to)) {
            throw new Exception("target: $to, already exists", 1);
        }

        return copy($this->root . $from, $this->root . $to);
    }

    /**
     * Remove a file
     *
     * @param string relative path of file
     *
     * @return bool true if the operation was successful, false otherwise
     */
    function remove($file)
    {
        $file = $this->normalise($file);
        return unlink($this->root . $file);
    }

    /**
     * @param string relative path of dir
     *
     * @return bool true if the dir exists, or false otherwise
     */
    private function _hasDir($dir)
    {
        return is_dir($this->root . $dir);
    }

    /**
     * Verifies if the filesystem has the dir
     *
     * @param string relative path of dir
     *
     * @return bool true if the dir exists, or false otherwise
     */
    function hasDir($dir)
    {
        $dir = $this->normalise($dir);
        return $this->_hasDir($dir);
    }

    /**
     * Creates a dir
     *
     * @param string relative path of dir
     * @param bool   true if the missing directories in the path could be created
     *
     * @return bool true if the operation was successful, false otherwise
     */
    function createDir($dir, $recursive = false)
    {
        $dir = $this->normalise($dir);

        if (!$recursive) {
            if ($dir && !is_dir($this->root . $dir)) {
                return mkdir($this->root . $dir);
            }

            return false;
        } else {
            $d = explode('/', $dir);
            $dd = '';
            $sep = '';

            while ($d) {
                $dd  = $dd . $sep . array_shift($d);
                $sep = '/';
                $r   = $this->createDir($dd);
            }

            return $r;
        }
    }

    /**
     * Removes a dir
     *
     * @param string relative path of dir
     * @param bool   true if the force to delete all files and subdirectories
     *
     * @return bool true if the operation was successful, false otherwise
     */
    function removeDir($dir, $force = false)
    {
        $dir = $this->normalise($dir);
        $list = $this->listDir($dir, true);

        if (!$force) {
            if ($dir !== '') {
                if (count($list) > 0)
                    return false;

                return rmdir($this->root . $dir);
            }
        } else {
            foreach($list as $item) {
                if ('file' === $item['type']) {
                    $this->remove($item['pathname']);
                } else {
                    $this->removeDir($item['pathname']);
                }
            }

            return $this->removeDir($dir);
        }
    }

    /**
     * List contents of a dir
     *
     * @param string relative path of dir
     * @param bool   true to recursively list the subdirectories as well
     *
     * @return array of elements, where each element is an array representing a file or a dir etc.
     */
    function listDir($dir, $recursive = false)
    {
        $dir = $this->normalise($dir);

        if ($this->_hasDir($dir)) {
            $r   = [];

            if ($recursive) {
                $it = new RecursiveDirectoryIterator($this->root . $dir, FilesystemIterator::SKIP_DOTS);
                $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

                foreach ($it as $i) {
                    $r[] = $this->_info($i);
                }
            } else {
                $it = new DirectoryIterator($this->root . $dir);

                foreach ($it as $i) {
                    if ($i->isDot()) {
                        continue;
                    }

                    $r[] = $this->_info($i);
                }
            }

            return $r;
        }

        return false;
    }

    /**
     * @param mixed
     *
     * @return array containing information of a file or a dir etc.
     */
    private function _info($item)
    {
        $pathname = str_replace($this->root, '', $item->getPathname());
        $pathname = preg_replace('|^\.\/|Us', '', $pathname);
        return [
            'pathname'    => $pathname,
            'basename'    => $item->getBasename(),
            'ext'         => $item->getExtension(),
            'accessed_on'    => $item->getATime(),
            'modified_on' => $item->getMTime(),
            'created_on'  => $item->getCTime(),
            'size'        => $item->getSize(),
            'type'        => $item->getType(),
            'mode'        => ($item->isReadable() ? 4 : 0)
                             + ($item->isWritable() ? 2 : 0)
                             + ($item->isExecutable() ? 1 : 0),
        ];
    }

    /**
     * Returns information regarding a file or a directory
     *
     * @param string filename or directory
     *
     * @return array containing information of a file or a dir etc.
     */
    function info($filename) {
        $filename = $this->normalise($filename);

        if ($this->_has($filename)) {
            $dir = dirname($filename);
            $it  = new DirectoryIterator($this->root . $dir);

            if ($dir === '.') {
                $dir = '';
            } else {
                $dir .= '/';
            }

            foreach ($it as $item) {
                if ($item->isDot()) {
                    continue;
                }

                if ($filename === $dir . $item->getBasename()) {
                    return $this->_info($item);
                }
            }
        } elseif ($this->_hasDir($filename)) {
            $it = new DirectoryIterator($this->root . $filename);
            return $this->_info($it);
        }

        return null;
    }
}
