<?php
/**
 * @author: Ivo Meißner
 * Date: 29.02.16
 * Time: 17:01
 */

namespace GraphQLRelay\tests;


use GraphQLRelay\Connection\ArrayConnection;
use GraphQLRelay\Connection\Connection;
use GraphQLRelay\Mutation\Mutation;
use GraphQLRelay\Node\Node;
use GraphQLRelay\Relay;

class RelayTest extends \PHPUnit_Framework_TestCase
{
    public function testForwardConnectionArgs()
    {
        $this->assertEquals(
            Connection::forwardConnectionArgs(),
            Relay::forwardConnectionArgs()
        );
    }

    public function testBackwardConnectionArgs()
    {
        $this->assertEquals(
            Connection::backwardConnectionArgs(),
            Relay::backwardConnectionArgs()
        );
    }

    public function testConnectionArgs()
    {
        $this->assertEquals(
            Connection::connectionArgs(),
            Relay::connectionArgs()
        );
    }
}