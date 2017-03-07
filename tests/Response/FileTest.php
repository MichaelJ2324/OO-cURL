<?php

namespace MRussell\Http\Tests\Response;

use MRussell\Http\Request\File as FileRequest;
use MRussell\Http\Request\RequestInterface;
use MRussell\Http\Response\File;

/**
 * Class FileTest
 * @package MRussell\Http\Tests\Response
 * @coversDefaultClass MRussell\Http\Response\File
 * @group response
 */
class FileTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
        $path = realpath(__DIR__.'/../Data/Responses');
        if (file_exists($path.'/response.txt')){
            unlink($path.'/response.txt');
            rmdir($path);
        }
        if (file_exists(sys_get_temp_dir().'/CurlFiles/response2.txt')){
            unlink(sys_get_temp_dir().'/CurlFiles/response2.txt');
        }
    }

    /**
     * @var RequestInterface
     */
    protected $Request;

    public function setUp()
    {
        FileRequest::autoInit(FALSE);
        $this->Request = new FileRequest('www.google.com');
        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->Request);
        parent::tearDown();
    }

    /**
     * @covers ::setFileName
     * @covers ::getFileName
     * @covers ::file
     * @return File
     */
    public function testSetFileName(){
        $Response = new File($this->Request);
        $Response->setFileName('test test.txt');
        $this->assertEquals('test_test.txt',$Response->getFileName());
        $this->assertEquals('/test_test.txt',$Response->file());
        $Response->setFileName('test~test.txt');
        $this->assertEquals('test~test.txt',$Response->getFileName());
        $this->assertEquals('/test~test.txt',$Response->file());
        $Response->setFileName('test/test.txt');
        $this->assertEquals('testtest.txt',$Response->getFileName());
        $this->assertEquals('/testtest.txt',$Response->file());
        $Response->setFileName('test\test.txt');
        $this->assertEquals('testtest.txt',$Response->getFileName());
        $this->assertEquals('/testtest.txt',$Response->file());
        $Response->setFileName('test;test.txt');
        $this->assertEquals('testtest.txt',$Response->getFileName());
        $this->assertEquals('/testtest.txt',$Response->file());
        $Response->setFileName(' test,test.txt');
        $this->assertEquals('_testtest.txt',$Response->getFileName());
        $this->assertEquals('/_testtest.txt',$Response->file());
        $Response->setFileName('test:test.txt');
        $this->assertEquals('testtest.txt',$Response->getFileName());
        $this->assertEquals('/testtest.txt',$Response->file());
        $Response->setFileName('response.txt');
        $this->assertEquals('response.txt',$Response->getFileName());
        $this->assertEquals('/response.txt',$Response->file());
        return $Response;
    }

    /**
     * @param File $Response
     * @depends testSetFileName
     * @covers ::setDestinationPath
     * @covers ::file
     * @group response
     * @return File
     */
    public function testSetDestination(File $Response){
        $path = realpath(__DIR__.'/../Data');
        $Response->setDestinationPath($path);
        $file = $Response->file();
        $this->assertEquals($path.'/response.txt',$file);
    }

    /**
     * @depends testSetDestination
     * @covers ::__construct
     * @covers ::extract
     * @covers ::writeFile
     * @covers ::getDefaultDestinationPath
     * @group response
     * @return File
     */
    public function testExtract(){
        $path = realpath(__DIR__.'/../Data');
        $path = $path."/Responses";
        $this->Request->send();
        $Response = new File($this->Request,$path,'response.txt');
        $this->assertFileExists($Response->file());
        unset($Response);
        $this->Request->reset();
        $Response = new File($this->Request);
        $Response->setFileName('response2.txt');
        $this->Request->send();
        $this->assertEquals(TRUE,$Response->extract());
        $this->assertEquals(sys_get_temp_dir().'/CurlFiles/response2.txt',$Response->file());
        $this->assertFileExists($Response->file());

        unset($Response);
        $this->Request->reset();
        $Response = new File($this->Request);
        $this->Request->send();
        $this->assertEquals(FALSE,$Response->extract());
        $this->assertEmpty($Response->getFileName());
        $this->assertEquals(sys_get_temp_dir().'/CurlFiles/',$Response->file());
        $this->assertEquals(FALSE,$Response->writeFile());
    }

}