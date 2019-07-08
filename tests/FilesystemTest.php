<?php

use IrfanTOOR\Filesystem;
use IrfanTOOR\Test;

class FilesystemTest extends Test
{
    protected $fs;

    function getFilesystem()
    {
        if (!$this->fs) {
            $root = __DIR__ . '/' . 'tmp';
            
            if (is_dir($root)) {
                system("rm -r $root");
            }

            mkdir($root);
            $this->fs = new Filesystem($root);
        }

        return $this->fs;
    } 

    function testInstance()
    {
        $fs = $this->getFilesystem();
        $this->assertInstanceOf(IrfanTOOR\Filesystem::class, $fs);
    }

    function testVersion()
    {
        $fs = $this->getFilesystem();
        $version = $fs::VERSION;

        $c = new \IrfanTOOR\Console();
        $c->write('(' . $version . ') ', 'dark');

        $this->assertString($version);
        $this->assertFalse(strpos($version, 'VERSION'));
        $this->assertEquals($fs::VERSION, Filesystem::VERSION);
    }

    function testHas()
    {
        $fs = $this->getFilesystem();
        $this->assertFalse($fs->has('file.txt'));
        
        $this->assertEquals(12, $fs->write('file.txt', 'Hello World!'));
        $this->assertTrue($fs->has('file.txt'));
    }

    function testRead()
    {
        $fs = $this->getFilesystem();
        $this->assertEquals('Hello World!', $fs->read('file.txt'));
        $this->assertFalse($fs->has('file1.txt'));
        $this->assertFalse($fs->read('file1.txt'));
    }

    function testWrite()
    {
        $fs = $this->getFilesystem();

        $this->assertException(
            function() use($fs){
                $fs->write('file.txt', 'something else');
            },
            Exception::class,
            'file: file.txt, already exists'
        );

        $this->assertNotZero($fs->write('file1.txt', 'something'));
        $this->assertEquals('something', $fs->read('file1.txt'));
        $this->assertNotZero($fs->write('file1.txt', 'something else', true));
        $this->assertEquals('something else', $fs->read('file1.txt'));
    }

    function testRename()
    {
        $fs = $this->getFilesystem();
        
        $this->assertTrue($fs->has('file1.txt'));
        $this->assertFalse($fs->has('file2.txt'));

        $contents = $fs->read('file1.txt');

        $this->assertTrue($fs->rename('file1.txt', 'file2.txt'));
        $this->assertFalse($fs->has('file1.txt'));
        $this->assertTrue($fs->has('file2.txt'));
        $this->assertEquals($contents, $fs->read('file2.txt'));

        $this->assertTrue($fs->has('file.txt'));
        $this->assertException(
            function() use($fs){
                $fs->rename('file.txt', 'file2.txt');
            },
            Exception::class,
            'target: file2.txt, already exists'
        );

        $this->assertFalse($fs->has('file1.txt'));
        $this->assertFalse($fs->has('file3.txt'));
        $this->assertException(
            function() use($fs) {
                $fs->rename('file1.txt', 'file3.txt');
            },
            Exception::class,
            'source: file1.txt, does not exist'
        );
    }

    function testCopy()
    {
        $fs = $this->getFilesystem();
        
        $this->assertTrue($fs->has('file.txt'));
        $this->assertFalse($fs->has('file1.txt'));

        $contents = $fs->read('file.txt');

        $this->assertNotZero($fs->copy('file.txt', 'file1.txt'));
        $this->assertTrue($fs->has('file.txt'));
        $this->assertTrue($fs->has('file1.txt'));
        $this->assertEquals($contents, $fs->read('file1.txt'));

        $this->assertException(
            function() use($fs) {
                $fs->copy('file.txt', 'file1.txt');
            },
            Exception::class,
            'target: file1.txt, already exists'
        );

        $this->assertFalse($fs->has('file3.txt'));
        $this->assertFalse($fs->has('file4.txt'));
        $this->assertException(
            function() use($fs) {
                $fs->rename('file3.txt', 'file4.txt');
            },
            Exception::class,
            'source: file3.txt, does not exist'
        );
    }

    function testCopyForce()
    {
        $fs = $this->getFilesystem();
        
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
        $this->assertException(
            function() use($fs) {
                $fs->copy('file4.txt', 'file5.txt', true);
            },
            Exception::class,
            'source: file4.txt, does not exist'
        );
    }

    function testRemove()
    {
        $fs = $this->getFilesystem();
        
        $this->assertTrue($fs->has('file.txt'));
        $this->assertTrue($fs->remove('file.txt'));
        $this->assertFalse($fs->has('file.txt'));
        $this->assertFalse($fs->remove('file.txt'));
    }

    function testHasDir()
    {
        $fs = $this->getFilesystem();
        $this->assertFalse($fs->hasDir('abc'));
        $this->assertFalse($fs->hasDir('file1.txt'));
        
        $fs->createDir('abc');
        $this->assertTrue($fs->hasDir('abc'));
    }

    function testCreateDir()
    {
        $fs = $this->getFilesystem();

        $this->assertFalse($fs->createDir('abc'));
        $this->assertFalse($fs->hasDir('abc/def'));
        $this->assertTrue($fs->createDir('abc/def'));
        $this->assertTrue($fs->hasDir('abc/def'));
    }

    function testRemoveDir()
    {
        $fs = $this->getFilesystem();

        $this->assertTrue($fs->hasDir('abc/def'));
        $this->assertFalse($fs->removeDir('abc'));
        $this->assertTrue($fs->hasDir('abc/def'));
        $this->assertTrue($fs->removeDir('abc/def'));
        $this->assertFalse($fs->hasDir('abc/def'));
        $this->assertTrue($fs->removeDir('abc'));
        $this->assertFalse($fs->hasDir('abc'));  
    }

    function testListDir()
    {
        $fs = $this->getFilesystem();

        $this->assertFalse($fs->listDir('abc'));
        $fs->createDir('abc');
        $this->assertArray($fs->listDir('abc'));
        $fs->write('abc/file.txt', 'Hello World! from abc');

        foreach ($fs->listDir('.') as $item) {
            $this->assertEquals($item['basename'], $item['pathname']);
            
            if ($item['basename'] == 'abc') {
                $this->assertEquals('', $item['ext']);
                $this->assertEquals('dir', $item['type']);
                $this->assertEquals(7, $item['mode']);                
            } else {
                $this->assertEquals('txt', $item['ext']);
                $this->assertEquals('file', $item['type']);
                $this->assertEquals(6, $item['mode']);                
            }
            
            $this->assertInt($item['accessed_on']);
            $this->assertInt($item['modified_on']);
            $this->assertInt($item['created_on']);
            $this->assertInt($item['size']);
        }

        foreach ($fs->listDir('abc') as $item) {
            $this->assertEquals('abc/' . $item['basename'], $item['pathname']);
            $this->assertEquals('txt', $item['ext']);
            $this->assertInt($item['accessed_on']);
            $this->assertInt($item['modified_on']);
            $this->assertInt($item['created_on']);
            $this->assertInt($item['size']);
            $this->assertEquals('file', $item['type']);
            $this->assertEquals(6, $item['mode']);
        }
    }

    function testInfo()
    {
        $fs = $this->getFilesystem();
        $root_info = $fs->info('/');

        $this->assertEquals('.', $root_info['pathname']);
        $this->assertEquals('.', $root_info['basename']);
        $this->assertEquals($root_info, $fs->info(''));
        $this->assertEquals($root_info, $fs->info('.'));
        $this->assertEquals($root_info, $fs->info('..'));
        $this->assertEquals($root_info, $fs->info('/'));
        $this->assertEquals($root_info, $fs->info('../../../'));
        $this->assertEquals($root_info, $fs->info('../../../..'));

        $root_info = $fs->info('/abc');

        $this->assertEquals('abc/.', $root_info['pathname']);
        $this->assertEquals('.', $root_info['basename']);
        $this->assertEquals($root_info, $fs->info('abc'));
        $this->assertEquals($root_info, $fs->info('abc/'));
        $this->assertEquals($root_info, $fs->info('/abc'));
        $this->assertEquals($root_info, $fs->info('/abc/'));
        $this->assertEquals($root_info, $fs->info('/abc/../../../'));

        $fs->createDir('etc');
        $this->assertEmpty($fs->listDir('/etc/'));

        $fs->removeDir('etc');
        $this->assertFalse($fs->listDir('/etc/'));

        $file_info = $fs->info('file1.txt');

        $this->assertEquals('file1.txt', $file_info['pathname']);
        $this->assertEquals('file1.txt', $file_info['basename']);
        $this->assertEquals('txt', $file_info['ext']);
        $this->assertNotNull($file_info['accessed_on']);
        $this->assertNotNull($file_info['modified_on']);
        $this->assertNotNull($file_info['created_on']);
        $this->assertInt($file_info['size']);
        $this->assertEquals('file', $file_info['type']);
        $this->assertEquals(6, $file_info['mode']);
    }

    function testCreateDirRecusrsive()
    {
        $fs = $this->getFilesystem();

        $this->assertFalse($fs->createDir('abc/def/ghi/jkl/mno/pqrs/tuv/wxyz'));
        $this->assertTrue($fs->createDir('abc/def/ghi/jkl/mno/pqrs/tuv/wxyz', true));
        $this->assertTrue($fs->createDir('wxyz', true));
        $this->assertFalse($fs->createDir('wxyz', true));

        $fs->removeDir('abc/def/ghi', true);
        $fs->removeDir('wxyz', true);
    }

    function testListDirRecusrsive()
    {
        $fs = $this->getFilesystem();

        $l1 = $fs->listDir('/');
        $l2 = $fs->listDir('abc');
        $list = $fs->listDir('/', true);

        $this->assertNotZero($l1);
        $this->assertNotZero($l2);
        $this->assertEquals(count($l1) + count($l2), count($list));

        $l = sort(array_merge($l1, $l2));
        $this->assertEquals($l, sort($list));
    }

    function testRemoveDirRecusrsive()
    {
        $fs = $this->getFilesystem();
        $fs->removeDir('abc');
        
        $this->assertTrue($fs->hasDir('abc'));
        
        $fs->createDir('abc/def');
        $fs->write('abc/def/file.text', 'a little deeper!');
        $fs->removeDir('abc', true);
        $this->assertFalse($fs->hasDir('abc'));
    }

    function testRemoveRoot()
    {
        $fs = $this->getFilesystem();

        $this->assertTrue($fs->hasDir('/'));
        $c = count($fs->listDir('/', true));
        $this->assertTrue($c > 1);

        # can not remove the root dir
        $fs->removeDir('/');
        $this->assertTrue($fs->hasDir('/'));
        $c = count($fs->listDir('/', true));
        $this->assertTrue($c > 1);

        $fs->removeDir('/', true);
        $c = $fs->listDir('/', true);
        $this->assertArray($c);
        $this->assertEmpty($c);

        $fs->removeDir('../', true);
        $fs->removeDir('..', true);
        $fs->removeDir('./../', true);
        $this->assertTrue($fs->hasDir('/'));
    }

    function testRootMustExist()
    {
        # finally remove the temporary root
        rmdir(__DIR__ . '/tmp');

        $this->assertException(
            function() {
                $fs = new Filesystem(__DIR__ . '/tmp');
            },
            Exception::class,
            'root dir: ' . __DIR__ . '/tmp/, does not exist'
        );
    }
}
