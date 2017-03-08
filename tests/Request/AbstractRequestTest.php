<?php

namespace SugarAPI\SDK\Tests\Request;

use MRussell\Http\Request\AbstractRequest;
use MRussell\Http\Request\RequestInterface;
use MRussell\Http\Request\Curl;
use MRussell\Http\Exception\InvalidHttpMethodException;

/**
 * Class AbstractResponseTest
 * @package MRussell\Http\Tests\Request\AbstractRequestTest
 * @coversDefaultClass MRussell\Http\Request\AbstractRequest
 * @group requests
 */
class AbstractRequestTest extends \PHPUnit_Framework_TestCase {

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
    
    protected $headers = array(
        'Authorization' => 'bearer 1234a',
        'X-TEST-Value' => 'Value',
        'X-Test-Cookie' => 'Cookie 1234'
    );

    protected $options = array(
          CURLOPT_TIMEOUT => 60,
          CURLOPT_COOKIE => 'Test'
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
     * @covers ::__construct
     * @covers ::init
     * @covers ::getStatus
     * @covers ::getOptions
     * @covers ::getStatusString
     * @covers ::getMethod
     * @covers ::getResponse
     * @covers ::getHeaders
     * @covers ::getCurlResource
     * @group abstractRequest
     * @return RequestInterface
     */
    public function testConstructor(){
        $Curl = new Curl();
        $this->assertEmpty($Curl->getURL());
        $this->assertEmpty($Curl->getBody());
        $this->assertEquals('GET',$Curl->getMethod());
        $this->assertEquals(Curl::STATUS_INIT,$Curl->getStatus());
        $this->assertEquals('Initialized',$Curl->getStatusString());
        $this->assertEmpty($Curl->getCurlResource());
        $this->assertEmpty($Curl->getResponse());
        $this->assertEmpty($Curl->getHeaders());
        $options = $Curl->getOptions();
        $this->assertEquals(TRUE,$options[CURLOPT_HEADER]);
        $this->assertEquals(CURL_HTTP_VERSION_1_0,$options[CURLOPT_HTTP_VERSION]);
        $this->assertEquals(FALSE,$options[CURLOPT_SSL_VERIFYPEER]);
        $this->assertEquals(TRUE,$options[CURLOPT_FOLLOWLOCATION]);
        $this->assertEquals(TRUE,$options[CURLOPT_RETURNTRANSFER]);
        unset($Curl);

        $Curl = new Curl($this->url,'POST');
        $this->assertEquals($this->url,$Curl->getURL());
        $this->assertEmpty($Curl->getBody());
        $this->assertEquals('POST',$Curl->getMethod());
        $this->assertEquals(Curl::STATUS_INIT,$Curl->getStatus());
        $this->assertEquals('Initialized',$Curl->getStatusString());
        $this->assertEmpty($Curl->getCurlResource());
        $this->assertEmpty($Curl->getResponse());
        $this->assertNotEmpty($Curl->getOptions());
        $this->assertEmpty($Curl->getHeaders());

        $options = $Curl->getOptions();
        $this->assertEquals(TRUE,$options[CURLOPT_HEADER]);
        $this->assertEquals(CURL_HTTP_VERSION_1_0,$options[CURLOPT_HTTP_VERSION]);
        $this->assertEquals(FALSE,$options[CURLOPT_SSL_VERIFYPEER]);
        $this->assertEquals(TRUE,$options[CURLOPT_FOLLOWLOCATION]);
        $this->assertEquals(TRUE,$options[CURLOPT_RETURNTRANSFER]);

        unset($Curl);

        $Curl = new Curl($this->url);
        $this->assertEquals($this->url,$Curl->getURL());
        $this->assertEmpty($Curl->getBody());
        $this->assertEquals('GET',$Curl->getMethod());
        $this->assertEquals(Curl::STATUS_INIT,$Curl->getStatus());
        $this->assertEquals('Initialized',$Curl->getStatusString());
        $this->assertEmpty($Curl->getCurlResource());
        $this->assertEmpty($Curl->getResponse());
        $this->assertNotEmpty($Curl->getOptions());
        $this->assertEmpty($Curl->getHeaders());
        
        $options = $Curl->getOptions();
        $this->assertEquals(TRUE,$options[CURLOPT_HEADER]);
        $this->assertEquals(CURL_HTTP_VERSION_1_0,$options[CURLOPT_HTTP_VERSION]);
        $this->assertEquals(FALSE,$options[CURLOPT_SSL_VERIFYPEER]);
        $this->assertEquals(TRUE,$options[CURLOPT_FOLLOWLOCATION]);
        $this->assertEquals(TRUE,$options[CURLOPT_RETURNTRANSFER]);

        return $Curl;
    }

    /**
     * @param AbstractRequest $Curl
     * @depends testConstructor
     * @covers ::__construct
     * @covers ::init
     * @covers ::initCurl
     * @covers ::getCurlResource
     * @covers ::getStatus
     * @covers ::getStatusString
     * @covers ::autoInit
     * @group abstractRequest
     * @return RequestInterface
     */
    public function testAutoInit(AbstractRequest $Curl){
        $this->assertEquals(FALSE,Curl::autoInit());
        $this->assertEquals(FALSE,$Curl->autoInit());
        Curl::autoInit(TRUE);
        $Another = new Curl($this->url);
        $this->assertNotEmpty($Another->getCurlResource());
        $this->assertEquals($Another->getStatus(),Curl::STATUS_CURL_INIT);
        $this->assertEquals($Another->getStatusString(),'Curl Initialized');
        $this->assertEquals($Curl->autoInit(),TRUE);
        $this->assertEquals($Another->autoInit(),TRUE);

        unset($Curl);
        $Curl = new Curl();
        $this->assertNotEmpty($Curl->getCurlResource());
        $this->assertEquals($Curl->getStatus(),Curl::STATUS_CURL_INIT);
        $this->assertEquals($Curl->getStatusString(),'Curl Initialized');
        $this->assertEquals($Curl->autoInit(),TRUE);

        unset($Another);
        unset($Curl);
        Curl::autoInit(FALSE);
        $Curl = new Curl($this->url);
        $this->assertEmpty($Curl->getCurlResource());
        $this->assertEquals($Curl->getStatus(),Curl::STATUS_INIT);
        $this->assertEquals($Curl->getStatusString(),'Initialized');
        $this->assertEquals($Curl->autoInit(),FALSE);

        return $Curl;
    }

    /**
     * @param RequestInterface $Curl
     * @depends testConstructor
     * @covers ::setURL
     * @covers ::getURL
     * @group abstractRequest
     * @return Curl
     */
    public function testSetUrl(RequestInterface $Curl){
        $Curl->setURL("https://local.foo.bar");
        $this->assertEquals("https://local.foo.bar",$Curl->getURL());
        $Curl->setURL("http://local.foo");
        $this->assertEquals("http://local.foo",$Curl->getURL());
        $Curl->setURL("192.168.1.20");
        $this->assertEquals("192.168.1.20",$Curl->getURL());
        $Curl->setUrl($this->url);
        $this->assertEquals($this->url,$Curl->getURL());
        return $Curl;
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetUrl
     * @covers ::__construct
     * @covers ::setMethod
     * @covers ::getMethod
     * @group abstractRequest
     * @return RequestInterface
     */
    public function testSetMethod(RequestInterface $Curl){
        $Curl->setMethod('get');
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_GET);
        $Curl->setMethod('Post');
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_POST);
        $Curl->setMethod('PUt');
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_PUT);
        $Curl->setMethod('DeLeTE');
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_DELETE);
        $Curl->setMethod(Curl::HTTP_HEAD);
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_HEAD);
        $Curl->setMethod('options');
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_OPTIONS);
        $Curl->setMethod('coNNect');
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_CONNECT);

        unset($Curl);

        $Curl = new Curl($this->url,'post');
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_POST);
        $Curl->setMethod(Curl::HTTP_GET);
        $this->assertEquals($Curl->getMethod(),Curl::HTTP_GET);

        return $Curl;
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetMethod
     * @covers ::setMethod
     * @expectedException MRussell\Http\Exception\InvalidHttpMethodException
     * @expectedExceptionMessageRegExp /Invalid HTTP Method/
     */
    public function testInvalidMethodException(RequestInterface $Curl){
        $Curl->setMethod('test');
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetMethod
     * @covers ::setBody
     * @covers ::getBody
     * @group abstractRequest
     * @return RequestInterface
     */
    public function testSetBody(RequestInterface $Curl){
        $Curl->setBody($this->body);
        $this->assertEquals($this->body,$Curl->getBody());
        $Curl->setBody(json_encode($this->body));
        $this->assertEquals(json_encode($this->body),$Curl->getBody());
        $Curl->setBody(array());
        $this->assertEquals(array(),$Curl->getBody());
        return $Curl;
    }

    /**
     * @param RequestInterface $Curl
     * @depends testSetBody
     * @covers ::setHeaders
     * @covers ::addHeaders
     * @covers ::addHeader
     * @covers ::getHeaders
     * @covers ::removeHeader
     * @group abstractRequest
     * @return RequestInterface
     */
    public function testSetHeaders(RequestInterface $Curl){
        $Curl->setHeaders($this->headers);
        $this->assertEquals($this->headers,$Curl->getHeaders());
        $Curl->setHeaders(array());
        $this->assertEquals(array(),$Curl->getHeaders());
        $Curl->addHeader('Authorization','bearer 1234a');
        $this->assertEquals(array(
            'Authorization' => 'bearer 1234a'
        ),$Curl->getHeaders());
        $twoTypedHeaders = array(
            'X-TEST-Value' => 'Value',
            'X-Test-Cookie: Cookie 1234'
        );
        $Curl->addHeaders($twoTypedHeaders);
        $this->assertEquals($this->headers,$Curl->getHeaders());
        $Curl->removeHeader('Authorization');
        $this->assertEquals(array(
            'X-TEST-Value' => 'Value',
            'X-Test-Cookie' => 'Cookie 1234'
        ),$Curl->getHeaders());
        $Curl->setHeaders(array());
        $this->assertEquals(array(),$Curl->getHeaders());
        return $Curl;
    }

    /**
     * @param AbstractRequest $Curl
     * @depends testSetHeaders
     * @covers ::defaultHeaders
     * @covers ::getHeaders
     * @group abstractRequest
     */
    public function testDefaultHeaders(AbstractRequest $Curl)
    {
        $defaultHeaders = $Curl->defaultHeaders();
        $this->assertEquals(TRUE,is_array($defaultHeaders));
        $Curl->defaultHeaders(array());
        unset($Curl);

        $this->assertEquals(array(),Curl::defaultHeaders());
        Curl::defaultHeaders($this->headers);
        $Curl = new Curl();
        $this->assertEquals($this->headers,$Curl->getHeaders());
        $this->assertEquals($this->headers,$Curl->defaultHeaders());
        Curl::defaultHeaders($defaultHeaders);
        $this->assertEquals($defaultHeaders,$Curl->defaultHeaders());
        unset($Curl);
        $this->assertEquals($defaultHeaders,Curl::defaultHeaders());
        $Curl = new Curl();
        $this->assertEquals($defaultHeaders,$Curl->getHeaders());
        $this->assertEquals($defaultHeaders,$Curl->defaultHeaders());
    }

    /**
     * @param AbstractRequest $Curl
     * @depends testSetHeaders
     * @covers ::setOptions
     * @covers ::addOption
     * @covers ::removeOption
     * @covers ::addOptions
     * @group abstractRequest
     * @return RequestInterface
     */
    public function testSetOptions(AbstractRequest $Curl){
        $Curl->setOptions($this->options);
        $this->assertEquals($this->options,$Curl->getOptions());
        $defaultOptions = $Curl->defaultOptions();
        $Curl->setOptions($defaultOptions);
        $this->assertEquals($defaultOptions,$Curl->getOptions());
        unset($Curl);

        $Curl = new Curl();
        $this->assertEquals($defaultOptions,$Curl->getOptions());
        $Curl->addOptions($this->options);
        $allOptions = array_replace($this->options,$defaultOptions);
        $this->assertEquals($allOptions,$Curl->getOptions());
        $Curl->setOptions(array());
        $this->assertEquals(array(),$Curl->getOptions());
        $Curl->addOption(CURLOPT_TIMEOUT,100);
        $this->assertEquals(array(
            CURLOPT_TIMEOUT => 100
        ),$Curl->getOptions());
        $Curl->removeOption(CURLOPT_TIMEOUT);
        $this->assertEquals(array(),$Curl->getOptions());
        return $Curl;
    }

    /**
     * @param AbstractRequest $Curl
     * @depends testSetOptions
     * @covers ::defaultOptions
     * @covers ::getOptions
     * @group abstractRequest
     */
    public function testDefaultOptions(AbstractRequest $Curl){
        $defaultOptions = $Curl->defaultOptions();
        $this->assertEquals(TRUE,is_array($defaultOptions));
        $Curl->defaultOptions(array());
        unset($Curl);

        $this->assertEquals(array(),Curl::defaultOptions());
        Curl::defaultOptions($this->options);
        $Curl = new Curl();
        $this->assertEquals($this->options,$Curl->getOptions());
        $this->assertEquals($this->options,$Curl->defaultOptions());
        Curl::defaultOptions($defaultOptions);
        $this->assertEquals($defaultOptions,$Curl->defaultOptions());
        unset($Curl);
        $this->assertEquals($defaultOptions,Curl::defaultOptions());
        $Curl = new Curl();
        $this->assertEquals($defaultOptions,$Curl->getOptions());
        $this->assertEquals($defaultOptions,$Curl->defaultOptions());
    }

    /**
     * @covers ::compileOptions
     * @covers ::configureHTTPMethod
     * @covers ::configureUrl
     * @covers ::configureHeaders
     * @covers ::configureBody
     * @covers ::configureOptions
     * @covers ::addCurlOption
     * @group abstractRequest
     * @return RequestInterface
     */
    public function testCompileOptions(){
        $Curl = new Curl();
        $Curl->setURL($this->url);
        $Curl->setHeaders($this->headers);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals($this->url,$CompiledOptions[CURLOPT_URL]);
        $this->assertEquals(array(
            0 => 'Authorization: bearer 1234a',
            1 => 'X-TEST-Value: Value',
            2 => 'X-Test-Cookie: Cookie 1234',
        ),$CompiledOptions[CURLOPT_HTTPHEADER]);
        $this->assertEquals(CURL_HTTP_VERSION_1_0,$CompiledOptions[CURLOPT_HTTP_VERSION]);
        $this->assertEquals(TRUE,$CompiledOptions[CURLOPT_HEADER]);
        $this->assertEquals(FALSE,$CompiledOptions[CURLOPT_SSL_VERIFYPEER]);
        $this->assertEquals(TRUE,$CompiledOptions[CURLOPT_RETURNTRANSFER]);
        $this->assertEquals(TRUE,$CompiledOptions[CURLOPT_FOLLOWLOCATION]);
        $Curl->setBody($this->body);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals($this->url.'?foo=bar',$CompiledOptions[CURLOPT_URL]);

        $Curl->setMethod(Curl::HTTP_POST);
        $Curl->setBody($this->body);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals(TRUE,$CompiledOptions[CURLOPT_POST]);
        $this->assertEquals($this->body,$CompiledOptions[CURLOPT_POSTFIELDS]);

        $Curl->setMethod(Curl::HTTP_PUT);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals(TRUE,$CompiledOptions[CURLOPT_PUT]);
        $this->assertEquals($this->body,$CompiledOptions[CURLOPT_POSTFIELDS]);

        $Curl->setMethod(Curl::HTTP_DELETE);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals(Curl::HTTP_DELETE,$CompiledOptions[CURLOPT_CUSTOMREQUEST]);
        $this->assertEquals($this->body,$CompiledOptions[CURLOPT_POSTFIELDS]);

        $Curl->setMethod(Curl::HTTP_HEAD);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals(Curl::HTTP_HEAD,$CompiledOptions[CURLOPT_CUSTOMREQUEST]);
        $this->assertEquals($this->body,$CompiledOptions[CURLOPT_POSTFIELDS]);

        $Curl->setMethod(Curl::HTTP_OPTIONS);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals(Curl::HTTP_OPTIONS,$CompiledOptions[CURLOPT_CUSTOMREQUEST]);
        $this->assertEquals($this->body,$CompiledOptions[CURLOPT_POSTFIELDS]);

        $Curl->setMethod(Curl::HTTP_CONNECT);
        $CompiledOptions = $Curl->getCurlOptions();
        $this->assertEquals(Curl::HTTP_CONNECT,$CompiledOptions[CURLOPT_CUSTOMREQUEST]);
        $this->assertEquals($this->body,$CompiledOptions[CURLOPT_POSTFIELDS]);

        return $Curl;
    }

    /**
     * @param AbstractRequest $Curl
     * @depends testSetOptions
     * @covers ::addFile
     * @covers ::compileOptions
     * @covers ::configureBody
     * @group abstractRequest
     */
    public function testAddFile(AbstractRequest $Curl){
        $filePath = realpath(__DIR__.'/../Data/test.txt');
        $Curl->addFile('filename',$filePath);
        $body = $Curl->getBody();
        $this->assertEquals(TRUE,isset($body['filename']));
        if (version_compare(PHP_VERSION, '5.5.0') >= 0){
            $this->assertEquals(TRUE,is_object($body['filename']));
        } else {
            $this->assertEquals('@'.$filePath,$body['filename']);
        }
        $Curl->addFile('file2',realpath(__DIR__.'test.txt'));
        $body = $Curl->getBody();
        $this->assertEquals(FALSE,isset($body['file2']));
        $CurlOptions = $Curl->getCurlOptions();
        $this->assertNotEmpty($CurlOptions[CURLOPT_HTTPHEADER]);
        $this->assertEquals('Content-Type: multipart/form-data',$CurlOptions[CURLOPT_HTTPHEADER][0]);
        $headers = $Curl->getHeaders();
        $this->assertEquals('multipart/form-data',$headers['Content-Type']);
    }

    /**
     * @covers ::getLegacyFileHandle
     */
    public function testLegacyFileHandle(){
        $filePath = realpath(__DIR__.'/../Data/test.txt');
        $Request = new Curl();
        $Class = new \ReflectionClass('MRussell\Http\Request\Curl');
        $method = $Class->getMethod('getLegacyFileHandle');
        $method->setAccessible(TRUE);
        $this->assertEquals('@'.$filePath,$method->invoke($Request,$filePath));
    }

    /**
     * @covers ::getFileHandle
     */
    public function testFileHandle(){
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            $filePath = realpath(__DIR__ . '/../Data/test.txt');
            $Request = new Curl();
            $Class = new \ReflectionClass('MRussell\Http\Request\Curl');
            $method = $Class->getMethod('getFileHandle');
            $method->setAccessible(TRUE);
            $CurlFile = $method->invoke($Request, $filePath);
            $this->assertEquals(TRUE, is_object($CurlFile));
            $this->assertInstanceOf('CURLFile', $CurlFile);
        }
    }

    /**
     * @covers ::getCurlResource
     * @covers ::getResponse
     * @covers ::getStatus
     * @covers ::send
     * @covers ::configureCurl
     * @covers ::executeCurl
     * @covers ::reset
     * @covers ::close
     * @covers ::closeCurl
     * @covers ::init
     * @covers ::checkForError
     * @covers ::error
     * @covers ::getError
     * @group abstractRequest
     */
    public function testCurl(){
        Curl::autoInit(TRUE);
        $Curl = new Curl($this->url);
        $CurlObject = $Curl->getCurlResource();
        $this->assertEquals(Curl::STATUS_CURL_INIT,$Curl->getStatus());
        $this->assertEquals('curl',get_resource_type($CurlObject));
        $Curl->close();
        $this->assertEquals(Curl::STATUS_CLOSED,$Curl->getStatus());
        if (strpos(PHP_VERSION,'7.0') === FALSE){
            $this->assertEquals(FALSE,is_resource($CurlObject));
        }
        $Curl->reset();
        $this->assertEquals(Curl::STATUS_CURL_INIT,$Curl->getStatus());
        $this->assertNotEquals($CurlObject,$Curl->getCurlResource());
        unset($Curl);
        unset($CurlObject);

        $Curl = new Curl('www.google.com');
        $CurlObject = $Curl->getCurlResource();
        $this->assertEquals(Curl::STATUS_CURL_INIT,$Curl->getStatus());
        $Curl->send();
        $this->assertEquals(Curl::STATUS_SENT,$Curl->getStatus());
        Curl::autoInit(FALSE);
        $Curl->reset();
        $Curl->setURL($this->url);
        $this->assertEquals(Curl::STATUS_INIT,$Curl->getStatus());
        $this->assertNotEquals($CurlObject,$Curl->getCurlResource());
        $this->assertEmpty($Curl->getCurlResource());
        $Curl->send();
        $CurlObject = $Curl->getCurlResource();
        $this->assertEquals(Curl::STATUS_SENT,$Curl->getStatus());
        $this->assertEquals('curl',get_resource_type($CurlObject));
        $Curl->send();
        $this->assertEquals(Curl::STATUS_SENT,$Curl->getStatus());
        $Curl->close();
        $this->assertEquals(Curl::STATUS_CLOSED,$Curl->getStatus());
        $Curl->send();
        $this->assertEquals(Curl::STATUS_CLOSED,$Curl->getStatus());
        $Curl->reset();
        $Curl->setURL('test.tester.test');
        $Curl->send();
        $this->assertEquals(Curl::STATUS_SENT,$Curl->getStatus());
        $this->assertEquals(TRUE,$Curl->error());
        $this->assertNotEmpty($Curl->getError());
        unset($Curl);
        unset($CurlObject);
    }

    /**
     * @covers ::__destruct
     * @group abstractRequest
     */
    public function testDestructor(){
        Curl::autoInit(TRUE);
        $Curl = new Curl($this->url);
        $CurlResource = $Curl->getCurlResource();
        unset($Curl);
        if (strpos(PHP_VERSION,'7.0') === FALSE){
            $this->assertEquals(FALSE,is_resource($CurlResource));
        }
    }
}
