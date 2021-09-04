<?php

namespace IrfanTOOR;

use Exception;
use FilesystemIterator;
use SplFileInfo;

class FileSystem
{
    const NAME        = "Irfan's Filesystem";
    const DESCRIPTION = "Irfan's Filesystem : A single-file minimum filesystem class to manage the files and directories.";
    const VERSION     = "0.5";

    protected $root;

    /**
     * Filesystem constructor
     *
     * @param string $root Root path of the FileSystem::class
     */
    function __construct(string $root)
    {
        $this->root = rtrim($root, '/') . '/';

        if ($this->root === "/")
            $this->root = "./";

        if (!is_dir($this->root))
            throw new Exception("dir: $this->root, does not exist");
    }

    /**
     * Normalizes the path
     *
     * @param string path
     *
     * @return string
     */
    function normalize($path): string
    {
        $path =
            rtrim(                                             # remove trailing  /+
                ltrim(                                         # remove preceding /+
                    preg_replace(
                        '|\/\/+|Us',                           #  /+ => /
                        '/',
                        str_replace(['../', './'], '/', $path) # ../ or ./ => /
                    ),                                         # order is important
                    '/'
                ),
                '/'
            )
        ;

        # like FilesystemIterator::SKIP_DOTS !
        return
            ($path === '.' || $path === '..')
            ? ''
            : $path
        ;
    }

    /**
     * Returns the absolute pathname of the provided relative path
     *
     * @param string $path Relative path of a file or a dir
     *
     * @return string
     */
    protected function pathname(string $path): string
    {
        return
            $this->root .
            $this->normalize($path)
        ;
    }

    /**
     * Returns the relative path of an absolute provided pathname
     * Note: it considers that the absolute path is under the current root of
     * FileSystem
     *
     * @param string $pathname Absolute pathname of a file or a directory
     *
     * @return string returns the relative path, by removeing the root path of
     *                FileSystem from the pathname
     */
    public function path(string $pathname): string
    {
        return
            ltrim(
                str_replace(
                    $this->root,
                    '',
                    $this->normalize($pathname)
                ),
                '/'
            )
        ;
    }

    /**
     * Returns FileSystemIterator of a given relative path
     *
     * @param string $dir Relative path of the directory
     *
     * @return FilesystemIterator
     */
    function dir($dir): FilesystemIterator
    {
        $pathname = $this->pathname($dir);

        if (!is_dir($pathname))
            throw new Exception("path: $dir, not found");

        return new FilesystemIterator($pathname , FilesystemIterator::SKIP_DOTS);
    }

    /**
     * Returns SplFileInfo Object of the given file/dir
     *
     * @param string $path Relative path of a file or a directory
     *
     * @return SplFileInfo|null
     */
    function file(string $path): ?SplFileInfo
    {
        $pathname = $this->pathname($path);

        return
            file_exists($pathname)
            ? new SplFileInfo($pathname)
            : null
        ;
    }

    /**
     * Verifies if the given path is a file
     *
     * @param $path Relative path of a file or a directory
     *
     * @return bool True if the provided path is a file, false otherwise
     */
    function isFile(string $path): bool
    {
        return is_file(
            $this->pathname($path)
        );
    }

    /**
     * Verifies if the given path is a directory
     *
     * @param $path Relative path of a file or a directory
     *
     * @return bool True if the provided path is a directory, false otherwise
     */
    function isDir(string $path): bool
    {
        return is_dir(
            $this->pathname($path)
        );
    }

    /**
     * Verifies if the given relative path is present in the FileSystem
     *
     * @param $file Relative path of a file
     *
     * @return bool True if the file exists and is a file, false otherwise
     */
    function has(string $file): bool
    {
        return is_file(
            $this->pathname($file)
        );
    }

    /**
     * Reads and returns the contents of the file
     *
     * @param string $file Relative path of a file
     *
     * @return string Contents of the file
     */
    function read(string $file): string
    {
        $pathname = $this->pathname($file);

        if (!file_exists($pathname))
            throw new Exception("file: $file, not found");

        return file_get_contents($pathname);
    }

    /**
     * Writes text to a file
     *
     * @param string $file     Relative path of a file
     * @param string $contents Contents to write to the file
     * @param bool   $force    Force writing even if the file already exists
     *
     * @return int Number of bytes written
     */
    function write(string $file, string $contents, bool $force = false): int
    {
        $pathname = $this->pathname($file);

        if (!$force && file_exists($pathname))
            throw new Exception("file: $file, already exists");

        return file_put_contents($pathname, $contents);
    }

    /**
     * Appends text to a file
     *
     * @param string $file     Relative path of a file
     * @param string $contents Contents to be appended to the file
     * @param bool   $create   Create the file if it does not exist
     *
     * @return int Number of bytes written
     */
    function append(string $file, string $contents, bool $create = false): int
    {
        $pathname = $this->pathname($file);

        if (!$create && !file_exists($pathname))
            throw new Exception("file: $file, does not exist");

        return file_put_contents($pathname, $contents, FILE_APPEND);
    }

    /**
     * Renames or moves a file from one location to another
     *
     * @param string $from  Relative path of the file to rename
     * @param string $to    New relative location and name
     * @param bool   $force Force the renaming operation, even if the target exists
     *
     * @return bool True if successful, false otherwise
     */
    function rename(string $from, string $to, bool $force = false): bool
    {
        $from_pathname = $this->pathname($from);
        if (!is_file($from_pathname))
            throw new Exception("source: $from, does not exist", 1);

        $to_pathname   = $this->pathname($to);
        if (!$force && is_file($to_pathname))
            throw new Exception("target: $to, already exists", 1);

        return rename($from_pathname, $to_pathname);
    }

    /**
     * Copies a file from one location to another
     *
     * @param string $from  Relative path of the source file
     * @param string $to    New relative location of the target
     * @param bool   $force Force the renaming operation, even if the target exists
     *
     * @return bool True if successful, false otherwise
     */
    function copy(string $from, string $to, bool $force = false): bool
    {
        $from_pathname = $this->pathname($from);
        if (!is_file($from_pathname))
            throw new Exception("source: $from, does not exist", 1);

        $to_pathname   = $this->pathname($to);
        if (!$force && is_file($to_pathname))
            throw new Exception("target: $to, already exists", 1);

        return copy($from_pathname, $to_pathname);
    }

    /**
     * Removes a file
     *
     * @param string $file Relative path of the file to remove
     *
     * @return bool True if successful, false otherwise
     */
    function remove(string $file): bool
    {
        $pathname = $this->pathname($file);

        if (!is_file($pathname))
            throw new Exception("file: $file, does not exist");

        return unlink($pathname);
    }

    /**
     * Verifies if the FileSystem has a directory
     * Note its an alias to isDir
     *
     * @param string $dir Relative path of the directory
     *
     * @return bool True if the path exists and is a directory, false otherwise
     */
    function hasDir($dir): bool
    {
        return $this->isDir($dir);
    }

    /**
     * Returns the contents of a directory as an array
     * e.g.
     * dd( $list = $fs->ls('/', true) );
     * will print something like :
     * Array (
     *      '0' => 'file1.txt',
     *      '1' => 'file2.txt',
     *      'subdir' => Array (
     *          '0' => 'Somefile',
     *          'AnotherSubDirectory' => Array (
     *              ...
     *          )
     *          ...
     *      )
     *      '2' => 'file3.txt,
     *      'subdir' => Array (),
     * )
     *
     * dd( $list = $fs->ls('/') );
     * will print something like :
     * Array (
     *      '0' => 'file1.txt',
     *      '1' => 'file2.txt',
     *      'subdir' => Array (),
     *      '2' => 'file3.txt,
     *      'subdir' => Array (),
     * )
     * Note: The empty array for 'subdir' in a non recursive call, even if it has
     * files or

     * @param string $dir       Relative path of the directory
     * @param bool   $recursive Make it true to scan the subdirectories recursively
     *
     * @return array List of the files or directories present in the given path
     */
    function ls(string $dir, bool $recursive = false): array
    {
        $list = [];

        foreach ($this->dir($dir) as $item) {
            $name = $item->getFilename();

            if ($item->isDir())
                $list[$name] = $recursive
                    ? $this->ls($dir . "/" . $name, true)
                    : []
                ;
            else
                $list[] = $name;
        }

        return $list;
    }

    /**
     * Makes a directory in the FileSystem
     *
     * @param string $dir       Relative path of the directory to make
     * @param bool   $recursive If the non existing base directories must also be
     *                          created recursively
     *
     * @return bool True if successful, false otherwise
     */
    function mkdir(string $dir, bool $recursive = false): bool
    {
        if ($recursive) {
            $d = explode('/', $dir);
            array_pop($d);
            $base = implode('/', $d);

            if (!$this->hasDir($base))
                $this->mkdir($base, true);

            return $this->mkdir($dir, false);
        } else {
            $pathname = $this->pathname($dir);

            # {{{
            # php: mkdir throws an uncatchable error
            # so, testing if the base dir really exists.
                $d = explode('/', $dir);
                array_pop($d);
                $base = implode('/', $d);

                if ($base === "")
                    $base = "/";
                if (!$this->isDir($base))
                    return false;
            # }}}

            try {
                return is_dir($pathname) ? false : mkdir($pathname);
            } catch(\Throwable $th) {
                return false;
            }
        }
    }

    /**
     * Removes an empty directory or remove all the contents recursively first
     *
     * @param string $dir       Relative path of the directory to remove
     * @param bool   $recursive True if remove the contents recursively, i.e. even
     *                          from any sub directories present.
     *
     * @return bool True if successful, false otherwise
     */
    function rmdir(string $dir, bool $recursive = false): bool
    {
        if ($recursive) {
            foreach ($this->dir($dir) as $item) {
                $path = str_replace(
                    $this->root,
                    '',
                    $item->getPathname()
                );

                if ($item->isFile())
                    $this->remove($path);
                else
                    $this->rmdir($path, true);
            }

            return $this->rmdir($dir, false);
        } else {
            $pathname = $this->pathname($dir);

            # {{{
            # php: rmdir throws an uncatchable error
            # so, testing if the dir is really empty.
            if(count($this->ls($dir)))
                return false;
            # }}}

            try {
                return
                    ($this->root !== $pathname)
                    ? rmdir( $pathname)
                    : false
                ;
            } catch (\Throwable $th) {
                return false;
            }
        }
    }

    /**
     * Returns FileSystem information regarding a file or a directory
     *
     * @param $path Relative path of a file or a directory
     *
     * @return array Array containing the FileSystem information of the object
     *               or an empty array otherwise
     */
    function info(string $path): array
    {
        $i = $this->file($path);

        return
            $i
            ? [
                'path'        => $i->getPath(),
                'pathname'    => $i->getPathname(),

                'basename'    => $i->getBasename(),
                'filename'    => $i->getFilename(),
                'extension'   => $i->getExtension(),

                'created_on'  => $i->getCtime(),
                'accessed_on' => $i->getAtime(),
                'modified_on' => $i->getMtime(),

                'group'       => $i->getGroup(),
                'owner'       => $i->getOwner(),
                'inode'       => $i->getINode(),

                'perms'       => $i->getPerms(),
                'size'        => $i->getSize(),
                'type'        => $i->getType(),

                'readable'    => $i->isReadable(),
                'writable'    => $i->isWritable(),
            ]
            : []
        ;
    }
}
