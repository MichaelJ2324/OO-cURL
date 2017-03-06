<?php

namespace SugarAPI\SDK\Tests\Request;

use MRussell\Http\Request\AbstractRequest;
use MRussell\Http\Request\Curl;
use MRussell\Http\Request\RequestInterface;

/**
 * Class GETTest
 * Tests GET Http Requests using the Request Object
 * @package MRussell\Http\Tests\Request
 * @coversDefaultClass MRussell\Http\Request\AbstractRequest
 * @group requests
 */
class GETTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    protected $url = 'localhost';

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
     * @covers ::setBody
     * @covers ::getCurlOptions
     * @covers ::compileOptions
     * @covers ::getBody
     * @covers ::configureBody
     * @covers ::configureUrl
     * @group requests
     * @return RequestInterface
     */
    public function testSetBody(){
        $Request = new Curl('localhost');
        $Request->setBody(array());
        $this->assertEquals(array(),$Request->getBody());
        $Request->setBody(array('test'));
        $this->assertEquals(array('test'),$Request->getBody());
        $curlOptions = $Request->getCurlOptions();
        $this->assertEquals('localhost?'.http_build_query(array('test')),$curlOptions[CURLOPT_URL]);
        $Request->setBody($this->body);
        $curlOptions = $Request->getCurlOptions();
        $this->assertEquals('localhost?'.http_build_query($this->body),$curlOptions[CURLOPT_URL]);
        $this->assertEquals($this->body,$Request->getBody());
        $Request->setBody("foo=bar&bar=foo");
        $curlOptions = $Request->getCurlOptions();
        $this->assertEquals('localhost?foo=bar&bar=foo',$curlOptions[CURLOPT_URL]);
        $this->assertEquals("foo=bar&bar=foo",$Request->getBody());
        return $Request;
    }

    /**
     * @param AbstractRequest $Request
     * @depends testSetBody
     * @covers ::send
     * @covers ::configureUrl
     * @covers ::configureBody
     * @covers ::compileOptions
     * @covers ::configureCurl
     * @group requests
     */
    public function testSend(AbstractRequest $Request){
        $Request->setBody($this->body);
        $Request->setURL('http://localhost');
        $Request->send();
        $curlOptions = $Request->getCurlOptions();
        $this->assertEquals('http://localhost?foo=bar',$curlOptions[CURLOPT_URL]);
        $this->assertEquals($this->body,$Request->getBody());
        unset($Request);

        $Request = new Curl('http://localhost?foo=bar');
        $Request->send();
        $curlOptions = $Request->getCurlOptions();
        $this->assertEquals('http://localhost?foo=bar',$curlOptions[CURLOPT_URL]);
        $this->assertEmpty($Request->getBody());
        unset($Request);

        $Request = new Curl('http://localhost?bar=foo');
        $Request->setBody($this->body);
        $Request->send();
        $curlOptions = $Request->getCurlOptions();
        $this->assertEquals('http://localhost?bar=foo&foo=bar',$curlOptions[CURLOPT_URL]);
        $this->assertEquals($this->body,$Request->getBody());
    }
}
