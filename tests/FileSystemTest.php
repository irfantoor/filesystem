<?php

use IrfanTOOR\FileSystem;
use IrfanTOOR\Test;

class FileSystemTest extends Test
{
    protected $fs;

    function getFileSystem()
    {
        if (!$this->fs) {
            $root = __DIR__ . '/' . 'tmp';

            if (is_dir($root)) {
                system("rm -r $root");
            }

            mkdir($root);
            $this->fs = new FileSystem($root);
        }

        return $this->fs;
    }

    function testInstance()
    {
        $fs = $this->getFileSystem();
        $this->assertInstanceOf(IrfanTOOR\FileSystem::class, $fs);
    }

    function testVersion()
    {
        $fs = $this->getFileSystem();
        $version = $fs::VERSION;

        $this->assertString($version);
        $this->assertFalse(strpos($version, 'VERSION'));
        $this->assertEquals($fs::VERSION, FileSystem::VERSION);
    }

    function testHas()
    {
        $fs = $this->getFileSystem();
        $this->assertFalse($fs->has('file.txt'));

        $this->assertEquals(12, $fs->write('file.txt', 'Hello World!'));
        $this->assertTrue($fs->has('file.txt'));
    }

    function testRead()
    {
        $fs = $this->getFileSystem();
        $this->assertEquals('Hello World!', $fs->read('file.txt'));
        $this->assertFalse($fs->has('file1.txt'));

    }

    /**
     * throws: Exception::class
     * message: file: file1.txt, not found
     */
    function testReadException()
    {
        $fs = $this->getFileSystem();
        $fs->read('file1.txt');
    }

    function testWrite()
    {
        $fs = $this->getFileSystem();

        $this->assertNotZero($fs->write('file1.txt', 'something'));
        $this->assertEquals('something', $fs->read('file1.txt'));
        $this->assertNotZero($fs->write('file1.txt', 'something else', true));
        $this->assertEquals('something else', $fs->read('file1.txt'));
    }

    /**
     * throws: Exception::class
     * message: file: file.txt, already exists
     */
    function testWriteException()
    {
        $fs = $this->getFileSystem();
        $fs->write('file.txt', 'something else');
    }

    function testRename()
    {
        $fs = $this->getFileSystem();

        $this->assertTrue($fs->has('file1.txt'));
        $this->assertFalse($fs->has('file2.txt'));

        $contents = $fs->read('file1.txt');

        $this->assertTrue($fs->rename('file1.txt', 'file2.txt'));
        $this->assertFalse($fs->has('file1.txt'));
        $this->assertTrue($fs->has('file2.txt'));
        $this->assertEquals($contents, $fs->read('file2.txt'));

        $this->assertFalse($fs->has('file1.txt'));
        $this->assertFalse($fs->has('file3.txt'));
    }

    /**
     * throws: Exception::class
     * message: source: file1.txt, does not exist
     */
    function testRenameSourceException()
    {
        $fs = $this->getFileSystem();
        $fs->rename('file1.txt', 'file3.txt');
    }

    /**
     * throws: Exception::class
     * message: target: file2.txt, already exists
     */
    function testRenameTargetException()
    {
        $fs = $this->getFileSystem();

        $this->assertTrue($fs->has('file.txt'));
        $fs->rename('file.txt', 'file2.txt');
    }

    function testCopy()
    {
        $fs = $this->getFileSystem();

        $this->assertTrue($fs->has('file.txt'));
        $this->assertFalse($fs->has('file1.txt'));

        $contents = $fs->read('file.txt');

        $this->assertNotZero($fs->copy('file.txt', 'file1.txt'));
        $this->assertTrue($fs->has('file.txt'));
        $this->assertTrue($fs->has('file1.txt'));
        $this->assertEquals($contents, $fs->read('file1.txt'));
        $this->assertFalse($fs->has('file3.txt'));
        $this->assertFalse($fs->has('file4.txt'));
    }

    /**
     * throws: Exception::class
     * message: source: file3.txt, does not exist
     */
    function testCopySourceException()
    {
        $fs = $this->getFileSystem();
        $fs->rename('file3.txt', 'file4.txt');
    }

    /**
     * throws: Exception::class
     * message: target: file1.txt, already exists
     */
    function testCopyTargetException()
    {
        $fs = $this->getFileSystem();
        $fs->copy('file.txt', 'file1.txt');
    }

    function testCopyForce()
    {
        $fs = $this->getFileSystem();

        $this->assertTrue($fs->has('file.txt'));
        $this->assertTrue($fs->has('file1.txt'));

        $contents = $fs->read('file.txt');

        $this->assertNotZero($fs->copy('file.txt', 'file1.txt', true));
        $this->assertTrue($fs->has('file.txt'));
        $this->assertTrue($fs->has('file1.txt'));
        $this->assertEquals($contents, $fs->read('file1.txt'));

        $fs->write('file3.txt', 'Its a test!');
        $this->assertNotZero($fs->copy('file3.txt', 'file1.txt', true));
        $this->assertEquals('Its a test!', $fs->read('file1.txt'));

        $this->assertFalse($fs->has('file4.txt'));
        $this->assertFalse($fs->has('file5.txt'));
    }

    /**
     * throws: Exception::class
     * message: source: file4.txt, does not exist
     */
    function testCopyForceException()
    {
        $fs = $this->getFileSystem();
        $fs->copy('file4.txt', 'file5.txt', true);
    }

    function testRemove()
    {
        $fs = $this->getFileSystem();

        $this->assertTrue($fs->has('file.txt'));
        $this->assertTrue($fs->remove('file.txt'));
        $this->assertFalse($fs->has('file.txt'));
    }

    /**
     * throws: Exception::class
     * message: file: file.txt, does not exist
     */
    function testRemoveException()
    {
        $fs = $this->getFileSystem();
        $this->assertFalse($fs->remove('file.txt'));
    }

    function testHasDir()
    {
        $fs = $this->getFileSystem();
        $this->assertFalse($fs->hasDir('abc'));
        $this->assertFalse($fs->hasDir('file1.txt'));

        $fs->mkdir('abc');
        $this->assertTrue($fs->hasDir('abc'));
    }

    function testmkdir()
    {
        $fs = $this->getFileSystem();

        $this->assertFalse($fs->mkdir('abc'));
        $this->assertFalse($fs->hasDir('abc/def'));
        $this->assertTrue($fs->mkdir('abc/def', true));
        $this->assertTrue($fs->hasDir('abc/def'));
    }

    function testrmdir()
    {
        $fs = $this->getFileSystem();
        $this->assertTrue($fs->hasDir('abc/def'));
        $this->assertFalse($fs->rmdir("abc"));
        $this->assertTrue($fs->hasDir('abc/def'));

        $this->assertTrue($fs->rmdir('abc/def'));
        $this->assertFalse($fs->hasDir('abc/def'));
        $this->assertTrue($fs->rmdir('abc'));
        $this->assertFalse($fs->hasDir('abc'));
    }

    /**
     * throws: Exception::class
     * message: path: abc, not found
     */
    function testlsExceptionNoPath()
    {
        $fs = $this->getFileSystem();
        $this->assertFalse($fs->ls('abc'));
    }

    function testls()
    {
        $fs = $this->getFileSystem();

        $fs->mkdir('abc');
        $this->assertArray($fs->ls('abc'));
        $fs->write('abc/file.txt', 'Hello World! from abc');

        # /abc
        $info = $fs->info('/abc');
        $this->assertEquals(
            $info['pathname'],
            $info['path'] . '/' . $info['basename']
        );

        $this->assertEquals('', $info['extension']);

        $this->assertInt($info['accessed_on']);
        $this->assertInt($info['modified_on']);
        $this->assertInt($info['created_on']);

        $this->assertInt($info['inode']);
        $this->assertInt($info['perms']);
        $this->assertInt($info['group']);
        $this->assertInt($info['owner']);
        $this->assertInt($info['size']);

        $this->assertString($info['type']);
        $this->assertEquals('dir', $info['type']);

        $this->assertBool($info['readable']);
        $this->assertBool($info['writable']);

        # abc/file.txt
        $info = $fs->info('/abc/file.txt');
        $this->assertEquals('file.txt', $info['basename']);
        $this->assertEquals('txt', $info['extension']);

        $this->assertInt($info['accessed_on']);
        $this->assertInt($info['modified_on']);
        $this->assertInt($info['created_on']);

        $this->assertInt($info['inode']);
        $this->assertInt($info['perms']);
        $this->assertInt($info['group']);
        $this->assertInt($info['owner']);
        $this->assertInt($info['size']);

        $this->assertString($info['type']);
        $this->assertEquals('file', $info['type']);

        $this->assertBool($info['readable']);
        $this->assertBool($info['writable']);
    }

    function testInfo()
    {
        $fs = $this->getFileSystem();
        $root_info = $fs->info('/');

        $this->assertEquals('tmp', $root_info['basename']);
        $this->assertEquals($root_info, $fs->info(''));
        $this->assertEquals($root_info, $fs->info('.'));
        $this->assertEquals($root_info, $fs->info('..'));
        $this->assertEquals($root_info, $fs->info('/'));
        $this->assertEquals($root_info, $fs->info('../../../'));
        $this->assertEquals($root_info, $fs->info('../../../..'));

        $root_info = $fs->info('/abc');
        $this->assertEquals('abc', $root_info['basename']);
        $this->assertEquals($root_info, $fs->info('abc'));
        $this->assertEquals($root_info, $fs->info('abc/'));
        $this->assertEquals($root_info, $fs->info('/abc'));
        $this->assertEquals($root_info, $fs->info('/abc/'));
        $this->assertEquals($root_info, $fs->info('/abc/../../../'));

        $fs->mkdir('etc');
        $this->assertEmpty($fs->ls('/etc/'));

        $fs->rmdir('etc');
        // todo -- $this->assertFalse($fs->ls('/etc/'));

        $file_info = $fs->info('file1.txt');

        $this->assertEquals('file1.txt', $file_info['basename']);
        $this->assertEquals('txt', $file_info['extension']);
        $this->assertInt($file_info['accessed_on']);
        $this->assertInt($file_info['modified_on']);
        $this->assertInt($file_info['created_on']);
        $this->assertInt($file_info['size']);
        $this->assertEquals('file', $file_info['type']);
    }

    function testmkdirRecusrsive()
    {
        $fs = $this->getFileSystem();

        $this->assertFalse($fs->mkdir('abc/def/ghi/jkl/mno/pqrs/tuv/wxyz'));
        $this->assertTrue($fs->mkdir('abc/def/ghi/jkl/mno/pqrs/tuv/wxyz', true));
        $this->assertTrue($fs->mkdir('wxyz', true));
        $this->assertFalse($fs->mkdir('wxyz', true));

        $fs->rmdir('abc/def/ghi', true);
        $fs->rmdir('wxyz', true);
    }

    function testlsRecusrsive()
    {
        $fs = $this->getFileSystem();

        $l1 = $fs->ls('/');
        $l2 = $fs->ls('abc');
        $list = $fs->ls('/', true);
        $this->assertNotZero($l1);
        $this->assertNotZero($l2);
        $this->assertEquals(count($l1), count($list));
        $this->assertEquals(count($l2), count($list['abc']));
    }

    function testrmdirRecusrsive()
    {
        $fs = $this->getFileSystem();
        $fs->rmdir('abc');
        $this->assertTrue($fs->hasDir('abc'));

        $fs->mkdir('abc/def');
        $fs->write('abc/def/file.text', 'a little deeper!');
        $fs->has('abc/def/file.text');
        $fs->rmdir('abc', true);
        $this->assertFalse($fs->hasDir('abc'));
    }

    function testRemoveRoot()
    {
        $fs = $this->getFileSystem();

        $this->assertTrue($fs->hasDir('/'));
        $c = count($fs->ls('/', true));
        $this->assertTrue($c > 1);

        # root is not empty
        $fs->rmdir('/');
        $this->assertTrue($fs->hasDir('/'));
        $c = count($fs->ls('/', true));
        $this->assertTrue($c > 1);

        # all items are removed
        $fs->rmdir('/', true);
        $c = $fs->ls('/', true);
        $this->assertArray($c);
        $this->assertEmpty($c);

        $fs->rmdir('../', true);
        $fs->rmdir('..', true);
        $fs->rmdir('./../', true);
        $this->assertTrue($fs->hasDir('/'));
    }

    /**
     * throws: Exception::class
     */
    function testRootMustExist()
    {
        # finally remove the temporary root
        $tmp_path = __DIR__ . '/tmp';
        rmdir($tmp_path);
        $fs = new FileSystem($tmp_path);
    }
}
