<?php
/**
 * @author: Ivo Meißner
 * Date: 29.02.16
 * Time: 12:11
 */

namespace GraphQLRelay\tests;


use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;

class StarWarsMutationTest extends TestCase
{
    public function testMutatesTheDataSet()
    {
        $mutation = 'mutation AddBWingQuery($input: IntroduceShipInput!) {
            introduceShip(input: $input) {
              ship {
                id
                name
              }
              faction {
                name
              }
              clientMutationId
            }
          }';

        $params = array (
            'input' =>
                array (
                    'shipName' => 'B-Wing',
                    'factionId' => '1',
                    'clientMutationId' => 'abcde',
                ),
        );

        $expected = array (
            'introduceShip' =>
                array (
                    'ship' =>
                        array (
                            'id' => 'U2hpcDo5',
                            'name' => 'B-Wing',
                        ),
                    'faction' =>
                        array (
                            'name' => 'Alliance to Restore the Republic',
                        ),
                    'clientMutationId' => 'abcde',
                ),
        );

        $result = GraphQL::executeQuery(StarWarsSchema::getSchema(), $mutation, null, null, $params)->toArray();

        $this->assertEquals(['data' => $expected], $result);
    }
}