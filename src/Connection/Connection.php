<?php
/**
 * @author: Ivo Meißner
 * Date: 22.02.16
 * Time: 17:15
 */

namespace GraphQLRelay\Connection;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Connection {
    /**
     * @var ObjectType
     */
    protected static $pageInfoType;

    /**
     * Returns a GraphQLFieldConfigArgumentMap appropriate to include on a field
     * whose return type is a connection type with forward pagination.
     *
     * @return array
     */
    public static function forwardConnectionArgs()
    {
        return [
            'after' => [
                'type' => Type::string()
            ],
            'first' => [
                'type' => Type::int()
            ]
        ];
    }

    /**
     * Returns a GraphQLFieldConfigArgumentMap appropriate to include on a field
     * whose return type is a connection type with backward pagination.
     *
     * @return array
     */
    public static function backwardConnectionArgs()
    {
        return [
            'before' => [
                'type' => Type::string()
            ],
            'last' => [
                'type' => Type::int()
            ]
        ];
    }

    /**
     * Returns a GraphQLFieldConfigArgumentMap appropriate to include on a field
     * whose return type is a connection type with bidirectional pagination.
     *
     * @return array
     */
    public static function connectionArgs()
    {
        return array_merge(
            self::forwardConnectionArgs(),
            self::backwardConnectionArgs()
        );
    }

    /**
     * Returns a GraphQLObjectType for a connection with the given name,
     * and whose nodes are of the specified type.
     */
    public static function connectionDefinitions(array $config)
    {
        if (!array_key_exists('nodeType', $config)){
            throw new \InvalidArgumentException('Connection config needs to have at least a node definition');
        }
        $nodeType = $config['nodeType'];
        $name = array_key_exists('name', $config) ? $config['name'] : $nodeType->name;
        $edgeFields = array_key_exists('edgeFields', $config) ? $config['edgeFields'] : [];
        $connectionFields = array_key_exists('connectionFields', $config) ? $config['connectionFields'] : [];
        $resolveNode = array_key_exists('resolveNode', $config) ? $config['resolveNode'] : null;
        $resolveCursor = array_key_exists('resolveCursor', $config) ? $config['resolveCursor'] : null;

        $edgeType = new ObjectType(array_merge([
            'name' => $name . 'Edge',
            'description' => 'An edge in a connection',
            'fields' => array_merge([
                'node' => [
                    'type' => $nodeType,
                    'resolve' => $resolveNode,
                    'description' => 'The item at the end of the edge'
                ],
                'cursor' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => $resolveCursor,
                    'description' => 'A cursor for use in pagination'
                ]
            ], self::resolveMaybeThunk($edgeFields)),
        ]));

        $connectionType = new ObjectType([
            'name' => $name . 'Connection',
            'description' => 'A connection to a list of items.',
            'fields' => array_merge([
                'pageInfo' => [
                    'type' => Type::nonNull(self::pageInfoType()),
                    'description' => 'Information to aid in pagination.'
                ],
                'edges' => [
                    'type' => Type::listOf($edgeType),
                    'description' => 'Information to aid in pagination'
                ]
            ], self::resolveMaybeThunk($connectionFields))
        ]);

        return [
            'edgeType' => $edgeType,
            'connectionType' => $connectionType
        ];
    }

    /**
     * The common page info type used by all connections.
     *
     * @return ObjectType
     */
    public static function pageInfoType()
    {
        if (self::$pageInfoType === null){
            self::$pageInfoType = new ObjectType([
                'name' => 'PageInfo',
                'description' => 'Information about pagination in a connection.',
                'fields' => [
                    'hasNextPage' => [
                        'type' => Type::nonNull(Type::boolean()),
                        'description' => 'When paginating forwards, are there more items?'
                    ],
                    'hasPreviousPage' => [
                        'type' => Type::nonNull(Type::boolean()),
                        'description' => 'When paginating backwards, are there more items?'
                    ],
                    'startCursor' => [
                        'type' => Type::string(),
                        'description' => 'When paginating backwards, the cursor to continue.'
                    ],
                    'endCursor' => [
                        'type' => Type::string(),
                        'description' => 'When paginating forwards, the cursor to continue.'
                    ]
                ]
            ]);
        }
        return self::$pageInfoType;
    }

    protected static function resolveMaybeThunk ($thinkOrThunk)
    {
        return is_callable($thinkOrThunk) ? call_user_func($thinkOrThunk) : $thinkOrThunk;
    }
}