<?php

namespace MRussell\Http\Tests\Request;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\File;
use MRussell\Http\Request\RequestInterface;
use MRussell\Http\Exception\InvalidHttpMethodException;

/**
 * Class POSTFileTest
 * @package MRussell\Http\Tests\Request
 * @coversDefaultClass MRussell\Http\Request\File
 * @group requests
 */
class FileTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }
    protected $body = array(
        'foo' => 'bar'
    );

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers ::setMethod
     * @covers ::configureHTTPMethod
     * @group requests
     */
    public function testSetMethod(){
        $Request = new File();
        $this->assertEquals(Curl::HTTP_POST,$Request->getMethod());
        $CurlOptions = $Request->getCurlOptions();
        $this->assertNotEmpty($CurlOptions[CURLOPT_HTTPHEADER]);
        $this->assertEquals('Content-Type: multipart/form-data',$CurlOptions[CURLOPT_HTTPHEADER][0]);
        $headers = $Request->getHeaders();
        $this->assertEquals('multipart/form-data',$headers['Content-Type']);

        $Request->setMethod(Curl::HTTP_GET);
        $CurlOptions = $Request->getCurlOptions();
        $this->assertEmpty($CurlOptions[CURLOPT_HTTPHEADER]);
        $this->assertEmpty($Request->getHeaders());

        $Request->setMethod(Curl::HTTP_PUT);
        $CurlOptions = $Request->getCurlOptions();
        $this->assertNotEmpty($CurlOptions[CURLOPT_HTTPHEADER]);
        $this->assertEquals('Content-Type: multipart/form-data',$CurlOptions[CURLOPT_HTTPHEADER][0]);
        $headers = $Request->getHeaders();
        $this->assertEquals('multipart/form-data',$headers['Content-Type']);

        return $Request;
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetMethod
     * @covers ::setMethod
     * @expectedException MRussell\Http\Exception\InvalidHttpMethodException
     * @expectedExceptionMessageRegExp /Invalid HTTP Method/
     */
    public function testInvalidOptionsMethod(RequestInterface $Curl){
        $Curl->setMethod(Curl::HTTP_OPTIONS);
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetMethod
     * @covers ::setMethod
     * @expectedException MRussell\Http\Exception\InvalidHttpMethodException
     * @expectedExceptionMessageRegExp /Invalid HTTP Method/
     */
    public function testInvalidHeadMethod(RequestInterface $Curl){
        $Curl->setMethod(Curl::HTTP_HEAD);
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetMethod
     * @covers ::setMethod
     * @expectedException MRussell\Http\Exception\InvalidHttpMethodException
     * @expectedExceptionMessageRegExp /Invalid HTTP Method/
     */
    public function testInvalidDeleteMethod(RequestInterface $Curl){
        $Curl->setMethod(Curl::HTTP_DELETE);
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetMethod
     * @covers ::setMethod
     * @expectedException MRussell\Http\Exception\InvalidHttpMethodException
     * @expectedExceptionMessageRegExp /Invalid HTTP Method/
     */
    public function testInvalidConnectMethod(RequestInterface $Curl){
        $Curl->setMethod(Curl::HTTP_CONNECT);
    }
}
