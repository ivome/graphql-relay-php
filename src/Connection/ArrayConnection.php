<?php
/**
 * @author: Ivo Meißner
 * Date: 22.02.16
 * Time: 18:54
 */

namespace GraphQLRelay\Connection;


class ArrayConnection
{
    const PREFIX = 'arrayconnection:';

    /**
     * Creates the cursor string from an offset.
     */
    public static function offsetToCursor($offset)
    {
        return base64_encode(self::PREFIX . $offset);
    }

    /**
     * Rederives the offset from the cursor string.
     */
    public static function cursorToOffset($cursor)
    {
        return intval(substr(base64_decode($cursor), strlen(self::PREFIX)));
    }

    /**
     * Given an optional cursor and a default offset, returns the offset
     * to use; if the cursor contains a valid offset, that will be used,
     * otherwise it will be the default.
     */
    public static function getOffsetWidthDefault($cursor, $defaultOffset)
    {
        if ($cursor == null){
            return $defaultOffset;
        }
        $offset = self::cursorToOffset($cursor);
        return $offset ? $defaultOffset : $offset;
    }

    /**
     * A simple function that accepts an array and connection arguments, and returns
     * a connection object for use in GraphQL. It uses array offsets as pagination,
     * so pagination will only work if the array is static.
     * @param array $data
     * @param $args
     *
     * @return array
     */
    public static function connectionFromArray(array $data, $args)
    {
        return self::connectionFromArraySlice($data, $args, [
            'sliceStart' => 0,
            'arrayLength' => count($data)
        ]);
    }

    /**
     * Given a slice (subset) of an array, returns a connection object for use in
     * GraphQL.
     *
     * This function is similar to `connectionFromArray`, but is intended for use
     * cases where you know the cardinality of the connection, consider it too large
     * to materialize the entire array, and instead wish pass in a slice of the
     * total result large enough to cover the range specified in `args`.
     *
     * @return array
     */
    public static function connectionFromArraySlice(array $arraySlice, $args, $meta)
    {
        $after = $args['after'];
        $before = $args['before'];
        $first = $args['first'];
        $last = $args['last'];
        $sliceStart = $meta['sliceStart'];
        $arrayLength = $meta['arrayLength'];
        $sliceEnd = $sliceStart + count($arraySlice);
        $beforeOffset = self::getOffsetWidthDefault($before, $arrayLength);
        $afterOffset = self::getOffsetWidthDefault($after, -1);

        $startOffset = max([
            $sliceStart - 1,
            $afterOffset,
            -1
        ]) + 1;

        $endOffset = min([
            $sliceEnd,
            $beforeOffset,
            $arrayLength
        ]);
        if ($first !== null) {
            $endOffset = min([
                $endOffset,
                $startOffset + $first
            ]);
        }

        if ($last !== null) {
            $startOffset = max([
                $startOffset,
                $endOffset - $last
            ]);
        }

        $slice = array_slice($arraySlice,
            max($startOffset - $sliceStart, 0),
            count($arraySlice) - ($sliceEnd - $endOffset)
        );

        $edges = array_map(function($item, $index) use ($startOffset) {
            return [
                'cursor' => self::offsetToCursor($startOffset + $index),
                'node' => $item
            ];
        }, $slice, array_keys($slice));

        $firstEdge = $edges[0];
        $lastEdge = $edges[count($edges) - 1];
        $lowerBound = $after ? ($afterOffset + 1) : 0;
        $upperBound = $before ? ($beforeOffset) : $arrayLength;

        return [
            'edges' => $edges,
            'pageInfo' => [
                'startCursor' => $firstEdge ? $firstEdge['cursor'] : null,
                'endCursor' => $lastEdge ? $lastEdge['cursor'] : null,
                'hasPreviousPage' => $last !== null ? $startOffset > $lowerBound : false,
                'hasNextPage' => $first !== null ? $endOffset < $upperBound : false
            ]
        ];
    }
}