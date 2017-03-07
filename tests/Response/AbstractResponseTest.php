<?php

namespace MRussell\Http\Tests\Response;

use MRussell\Http\Request\Curl;
use MRussell\Http\Request\RequestInterface;
use MRussell\Http\Response\Standard;
use MRussell\Http\Tests\Stubs\ResponseTestStub;

/**
 * Class AbstractResponseTest
 * @package MRussell\Http\Tests\Response\AbstractResponseTest
 * @coversDefaultClass MRussell\Http\Response\AbstractResponse
 * @group responses
 */
class AbstractResponseTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    /**
     * @var RequestInterface
     */
    protected $Request;

    public function setUp()
    {
        $this->Request = new Curl('www.google.com');
        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->Request);
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @covers ::extract
     * @covers ::extractInfo
     * @covers ::extractResponse
     * @covers ::getInfo
     * @covers ::getBody
     * @covers ::getHeaders
     * @covers ::getStatus
     * @group abstractResponse
     */
    public function testConstructor(){
        $Response = new Standard($this->Request);
        $this->assertEmpty($Response->getInfo());
        $this->assertEmpty($Response->getBody());
        $this->assertEmpty($Response->getHeaders());
        $this->assertEmpty($Response->getStatus());
        $this->assertEquals(FALSE,$Response->extract());
        $this->Request->send();
        $this->assertEquals(TRUE,$Response->extract());
        $this->assertNotEmpty($Response->getInfo());
        $this->assertNotEmpty($Response->getBody());
        $this->assertNotEmpty($Response->getHeaders());
        $this->assertNotEmpty($Response->getStatus());
    }

    public function testExtraInfo(){
        $Response = new ResponseTestStub($this->Request);
        $this->Request->send();
        $this->assertEquals(TRUE,$Response->extract());
        $info = $Response->getInfo();
        $this->assertEquals(TRUE,isset($info[CURLINFO_SSL_VERIFYRESULT]));
        $this->assertEquals(TRUE,isset($info[CURLINFO_REDIRECT_COUNT]));
    }
}
