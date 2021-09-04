Irfan's Filesystem
==================

A single-file minimum filesystem class, to manage the files and directories.

## 1. Installation

Install the latest version with

```sh
composer require irfantoor/filesystem
```

Note: Irfan's Filesystem requires PHP 7.1 or newer.

## Usage

## Initialising

```php
<?php

use IrfanTOOR\Filesystem;

$fs = new Filesystem('/path/to/mount/as/root/'); // the terminating '/' in path is optional
echo $fs->getVersion(); // reports the version of this package

```

## Files

The basic functions related to files are explained through the following examples:

```php
$fs = new IrfanTOOR\Filesystem('My/Base/Path');

# Verifies if the filesystem has the file.
$fs->has('file.txt'); // returns true if the filesystem has the file, or false otherwise

# Reads and returns the contnets of a file
$contents = $fs->read('file.txt');

# Writes to a file
# write($file, $contents, $force = false)
$fs->write('file1.txt', 'Hello World!'); // writes to file if it does not exists
$fs->write('file1.txt', 'Something'); // throws an Exception, as the file already exists
$fs->write('file1.txt', 'Hello World!', true); // forces to write to file, even if it exists

# Rename a file
$fs->rename('hello.txt', 'world.txt'); // returns true if the operation was successful

# Copy a file
# format: $fs->copy($from, $to, $force = false)
$fs->copy('somefile', 'another_file'); // throws an Exception, if the source does not exists
$fs->copy('somefile', 'another_file'); // copies to the target provided target doest not exist
$fs->copy('somefile', 'another_file'); // throws an Exception, if the target exists
$fs->copy('somefile', 'another_file', true); // copies to the target even if the target exists

# Remove a file
# format: $fs->remove($file)
$fs->remove('another_file'); // removes the file, if it exists
$fs->remove('another_file'); // throws an Exception if the file does not exist
```

## Directories

The basic operations related to directories are explained through examples as follows:

```php
$fs = new IrfanTOOR\Filesystem('My/Base/Path');

# Verifies if the filesystem has the dir
# format: $fs->hasDir($dir)
if ($fs->hasDir('abc')) { // verifies if the directory : My/Base/Path/abc exists
    # ...
}

# Creates a directory in the FileSystem
# format: mkdir(string $dir, bool $recursive = false): bool
$fs->mkdir('def'); // creates directory : My/Base/Path/def
$fs->mkdir('ghi/jkl'); // returns false if directory My/Base/Path/ghi does not exist
$fs->mkdir('ghi/jkl/mno', true); // Creates all of the missing directories in the path

# Removes a dir
# format: rmdir(string $dir, bool $recursive = false): bool
$fs->rmdir('abc'); // removes the relative dir, if its empty
$fs->rmdir('ghi'); // fails if it contains files or sub-directory
$fs->rmdir('ghi', true); // forces to remove all of the files and sub-folders
$fs->rmdir('/', true); // this operation will delete every thing except removing the rootpath

# Returns the contents of a directory as an array
# format: ls(string $dir, bool $recursive = false): array
$list = $fs->ls('abc'); // retuens the list of the entries of abc as an array
$list = $fs->ls('ghi'); // retuens the list of the entries of ghi
$list = $fs->ls('ghi', true); // retuens the list of the entries of ghi and all the sub-directories
```

## Common

The functions which can be used for both files and directories.

```php
# ...

# Returns FileSystem information regarding a file or a directory
# format: $fs->info($filename)
print_r($fs->info('abc'));
/*
Array
(
    [path] => "\/Users\/dev\/hosts\/github\/irfantoor\/filesystem"
    [pathname] => "\/Users\/dev\/hosts\/github\/irfantoor\/filesystem\/tests"
    [basename] => "tests"
    [filename] => "tests"
    [extension] => ""
    [created_on] => 1630761810
    [accessed_on] => 1630761811
    [modified_on] => 1630761810
    [group] => 20
    [owner] => 502
    [inode] => 526945
    [perms] => 16877
    [size] => 96
    [type] => "dir"
    [readable] => true
    [writable] => true
)
*/

print_r($fs->info('tests/FileSystemTest.php'));
/*
Array
(
    [path] => "\/Users\/dev\/hosts\/github\/irfantoor\/filesystem\/tests"
    [pathname] => "\/Users\/dev\/hosts\/github\/irfantoor\/filesystem\/tests\/FileSystemTest.php"
    [basename] => "FileSystemTest.php"
    [filename] => "FileSystemTest.php"
    [extension] => "php"
    [created_on] => 1630515548
    [accessed_on] => 1630761810
    [modified_on] => 1630515548
    [group] => 20
    [owner] => 502
    [inode] => 2370274
    [perms] => 33188
    [size] => 14593
    [type] => "file"
    [readable] => true
    [writable] => true
)
*/
```

## About

**Requirements**
Irfan's Filesystem works with PHP 7.3 or above.

**License**

Irfan's Filesystem is licensed under the GPL v3.0 License - see the `LICENSE` file for details
