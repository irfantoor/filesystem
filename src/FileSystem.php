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

    protected function pathname(string $path): string
    {
        return
            $this->root .
            $this->normalize($path)
        ;
    }

    public function relativePath(string $pathname)
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

    function dir($path): FilesystemIterator
    {
        $pathname = $this->pathname($path);

        if (!is_dir($pathname))
            throw new Exception("path: $path, not found");

        return new FilesystemIterator($pathname , FilesystemIterator::SKIP_DOTS);
    }

    function file(string $filename)
    {
        $pathname = $this->pathname($filename);

        return
            file_exists($pathname)
            ? new SplFileInfo($pathname)
            : null
        ;
    }

    function isFile(string $filename)
    {
        return is_file(
            $this->pathname($filename)
        );
    }

    function isDir(string $path)
    {
        return is_dir(
            $this->pathname($path)
        );
    }

    function has(string $filename)
    {
        return file_exists(
            $this->pathname($filename)
        );
    }

    function read(string $filename): string
    {
        $pathname = $this->pathname($filename);

        if (!file_exists($pathname))
            throw new Exception("file: $filename, not found");

        return file_get_contents($pathname);
    }

    function write(string $filename, string $contents, bool $force = false): int
    {
        $pathname = $this->pathname($filename);

        if (!$force && file_exists($pathname))
            throw new Exception("file: $filename, already exists");

        return file_put_contents($pathname, $contents);
    }

    function rename(string $from, string $to, bool $force = false): bool
    {
        $from_pathname = $this->pathname($from);
        $to_pathname   = $this->pathname($to);

        if (!is_file($from_pathname))
            throw new Exception("source: $from, does not exist", 1);

        if (!$force && is_file($to_pathname))
            throw new Exception("target: $to, already exists", 1);

        return rename($from_pathname, $to_pathname);
    }

    function copy(string $from, string $to, bool $force = false): bool
    {
        $from_pathname = $this->pathname($from);
        $to_pathname   = $this->pathname($to);

        if (!is_file($from_pathname))
            throw new Exception("source: $from, does not exist", 1);

        if (!$force && is_file($to_pathname))
            throw new Exception("target: $to, already exists", 1);

        return copy($from_pathname, $to_pathname);
    }

    function remove(string $file): bool
    {
        $pathname = $this->pathname($file);

        if (!is_file($pathname))
            throw new Exception("file: $file, does not exist", 1);

        return unlink($pathname);
    }

    function hasDir($dir)
    {
        return $this->isDir($dir);
    }

    function ls(string $dir, bool $recursive = false): array
    {
        $list = [];

        foreach ($this->dir($dir) as $item) {
            if ($recursive) {
                if ($item->isFile()) {
                    $list[] = $item->getFilename();
                } elseif ($item->isDir()) {
                    $d = $item->getFilename();
                    $list[$d] = $this->ls($dir . "/" . $d, true);
                    // [
                        // 'pathname' => $this->relativePath($item->getPathname()),
                        // 'list' => $this->ls($dir . "/" . $d, true)
                    // ];
                }
            } else {
                // if ($item->isFile()) {
                    $list[] = $item->getFilename();
                // } elseif ($item->isDir()) {
                    // $list[] = "" . $item->getFilename();
                // }
            }
        }

        return $list;
    }

    function mkdir(string $dir, bool $recursive = false)
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

    function rmdir(string $dir, bool $recursive = false)
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

            $this->rmdir($dir, false);
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

    function info(string $file)
    {
        $i = $this->file($file);

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
