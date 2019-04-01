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
$fs->write('file1.txt', 'Hello World!', true); // forces to write to file, even if it exists

# Rename a file
$fs->rename('hello.txt', 'world.txt'); // returns true if the operation was successful

# Copy a file
# format: $fs->copy($from, $to, $force = false)
$fs->copy('somefile', 'another_file'); // copies to the target provided target doest not exist
$fs->copy('somefile', 'another_file', true); // copies to the target even if the target exists

# Remove a file
# format: $fs->remove($file)
$fs->remove('another_file');
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

# Creates a dir
# format: $fs->createDir($dir, $recursive = false)
$fs->createDir('def'); // creates directory : My/Base/Path/def
$fs->createDir('ghi/jkl'); // returns false if directory My/Base/Path/ghi does not exist
$fs->createDir('ghi/jkl/mno', true); // tries to create all of the missing directories in the path

# Removes a dir
# format: $fs->removeDir($dir, $force = false)
$fs->removeDir('abc'); // removes the relative dir, if its empty
$fs->removeDir('ghi'); // fails if it contains files or sub-directory
$fs->removeDir('ghi', true); // forces to remove all of the files and sub-folders
$fs->removeDir('/', true); // this operation will delete every thing except removing the rootpath


# List contents of a dir
# format: $fs->listDir($dir, $recursive = false)
$list = $fs->listDir('abc'); // retuens the list of the entries of abc as an array
$list = $fs->listDir('ghi'); // retuens the list of the entries of ghi
$list = $fs->listDir('ghi', true); // retuens the list of the entries of ghi and all the sub-directories
```

## Common

to retreive the information regarding an element, be it a directory or a file, function info can be used.

```php
# ...

# Returns information regarding a file or a directory
# format: $fs->info($filename)
print_r($fs->info('abc'));
/*
Array
(
    [pathname] => abc/.
    [basename] => .
    [ext] => 
    [accessed_on] => 1554135875
    [modified_on] => 1554135524
    [created_on] => 1554135524
    [size] => 96
    [type] => dir
    [mode] => 7
)
*/
print_r($fs->info('file1.txt'));
/*
Array
(
    [pathname] => file.txt
    [basename] => file.txt
    [ext] => txt
    [accessed_on] => 1554135822
    [modified_on] => 1554134609
    [created_on] => 1554134609
    [size] => 11
    [type] => file
    [mode] => 6
)
*/
```

## About

**Requirements**
Irfan's Filesystem works with PHP 7.1 or above.

**License**

Irfan's Filesystem is licensed under the MIT License - see the `LICENSE` file for details
