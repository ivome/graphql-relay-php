<?php
/**
 * @author: Ivo Meißner
 * Date: 22.02.16
 * Time: 18:35
 */
namespace GraphQLRelay\Tests\Connection;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQLRelay\Connection\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $allUsers;

    /**
     * @var \GraphQL\Type\Definition\ObjectType
     */
    protected static $userType;

    /**
     * @var array
     */
    protected static $friendConnection;

    /**
     * @var array
     */
    protected static $userConnection;

    public function setup()
    {
        self::$allUsers = [
            [ 'name' => 'Dan', 'friends' => [1, 2, 3, 4] ],
            [ 'name' => 'Nick', 'friends' => [0, 2, 3, 4] ],
            [ 'name' => 'Lee', 'friends' => [0, 1, 3, 4] ],
            [ 'name' => 'Joe', 'friends' => [0, 1, 2, 4] ],
            [ 'name' => 'Tim', 'friends' => [0, 1, 2, 3] ],
        ];

        self::$userType = new ObjectType([
            'name' => 'User',
            'fields' => [
                'name' => [
                    'type' => Type::string()
                ],
                'friends' => [
                    'type' => self::$friendConnection,
                    'args' => Connection::connectionArgs(),
                    'resolve' => function ($user, $args) {

                    }
                ]
            ]
        ]);
    }

    public function testIncludesConnectionAndEdgeFields()
    {

    }
}