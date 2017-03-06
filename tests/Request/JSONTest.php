<?php

namespace MRussell\Http\Tests\Request;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\JSON;
use MRussell\Http\Request\RequestInterface;
use MRussell\Http\Exception\InvalidHttpMethodException;

/**
 * Class POSTJSONTest
 * @package MRussell\Http\Tests\Request
 * @coversDefaultClass MRussell\Http\Request\JSON
 * @group requests
 */
class JSONTest extends \PHPUnit_Framework_TestCase {

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
     * @covers ::configureBody
     * @group requests
     */
    public function testSetMethod(){
        $Request = new JSON();
        $this->assertEquals(Curl::HTTP_GET,$Request->getMethod());
        $this->assertEquals(array(
            "Content-Type" => "application/json"
        ),$Request->getHeaders());
        $Request->setBody(array('foo' => "bar"));
        $CurlOptions = $Request->getCurlOptions();
        $this->assertNotEmpty($CurlOptions[CURLOPT_HTTPHEADER]);
        $this->assertEquals('Content-Type: application/json',$CurlOptions[CURLOPT_HTTPHEADER][0]);
        $headers = $Request->getHeaders();
        $this->assertEquals('application/json',$headers['Content-Type']);
        $Request->setMethod(JSON::HTTP_POST);
        $CurlOptions = $Request->getCurlOptions();
        $this->assertEquals(json_encode(array('foo' => "bar")),$CurlOptions[CURLOPT_POSTFIELDS]);

    }
}
