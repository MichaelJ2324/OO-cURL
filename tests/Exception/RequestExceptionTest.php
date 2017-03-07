<?php

namespace MRussell\Http\Tests\Exception;

use MRussell\Http\Exception\RequestException;

/**
 * Class RequestExceptionTest
 * @package MRussell\Http\Tests\Exception
 * @coversDefaultClass MRussell\Http\Exception\RequestException
 * @group Exceptions
 */
class RequestExceptionTest extends \PHPUnit_Framework_TestCase
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
        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->Curl);
        parent::tearDown();
    }

    /**
     * @covers ::__construct
     * @expectedException MRussell\Http\Exception\RequestException
     * @expectedExceptionMessageRegExp /Unknown Exception occurred in Request Object/
     */
    public function testInvalidMethodException(){
        throw new RequestException(__CLASS__);
    }
}