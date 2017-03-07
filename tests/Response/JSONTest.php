<?php

namespace MRussell\Http\Tests\Response;

use MRussell\Http\Request\JSON as JSONRequest;
use MRussell\Http\Response\JSON;

/**
 * Class JSONTest
 * @package MRussell\Http\Tests\Response
 * @coversDefaultClass MRussell\Http\Response\JSON
 * @group responses
 */
class JSONTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    /**
     * @var \MRussell\Http\Request\JSON
     */
    protected $Request;

    public function setUp()
    {
        $this->Request = new JSONRequest('https://scarlett.sugarondemand.com/rest/v10/releases');
        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->Curl);
        parent::tearDown();
    }

    /**
     * @covers ::getJson
     * @covers ::getBody
     * @group jsonResponse
     */
    public function testJson(){
        $Response = new JSON($this->Request);
        $this->assertEmpty($Response->getInfo());
        $this->assertEmpty($Response->getBody());
        $this->assertEmpty($Response->getHeaders());
        $this->assertEmpty($Response->getStatus());
        $this->assertEmpty($Response->getJson());
        $this->Request->send();
        $this->assertEquals(TRUE,$Response->extract());
        $this->assertNotEmpty($Response->getInfo());
        $body = $Response->getBody();
        $this->assertNotEmpty($body);
        $this->assertEquals(TRUE,is_array($body));
        $json = $Response->getJson();
        $this->assertEquals('string',gettype($json));
        $this->assertEquals($body,json_decode($json,TRUE));
        $this->assertNotEmpty($Response->getHeaders());
        $this->assertNotEmpty($Response->getStatus());
    }
}
