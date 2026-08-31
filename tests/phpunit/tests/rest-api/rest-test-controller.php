<?php

declare(strict_types=1);
/**
 * Unit tests covering WP_REST_Controller functionality
 *
 * @package WordPress
 * @subpackage REST API
 *
 * @group restapi
 */
class WP_REST_Test_Controller extends WP_REST_Controller
{
    /**
     * Prepares the item for the REST response.
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function prepare_item_for_response($item, $request)
    {
        $context  = ! empty($request['context']) ? $request['context'] : 'view';
        $item     = $this->add_additional_fields_to_object($item, $request);
        $item     = $this->filter_response_by_context($item, $context);
        $response = rest_ensure_response($item);
        return $response;
    }

    /**
     * Get the item's schema, conforming to JSON Schema.
     *
     * @return array
     */
    public function get_item_schema()
    {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'type',
            'type'       => 'object',
            'properties' => [
                'somestring'        => [
                    'type'        => 'string',
                    'description' => 'A pretty string.',
                    'minLength'   => 3,
                    'maxLength'   => 3,
                    'pattern'     => '[a-zA-Z]+',
                    'context'     => [ 'view' ],
                ],
                'someinteger'       => [
                    'type'             => 'integer',
                    'multipleOf'       => 10,
                    'minimum'          => 100,
                    'maximum'          => 200,
                    'exclusiveMinimum' => true,
                    'exclusiveMaximum' => true,
                    'context'          => [ 'view' ],
                ],
                'someboolean'       => [
                    'type'    => 'boolean',
                    'context' => [ 'view' ],
                ],
                'someurl'           => [
                    'type'    => 'string',
                    'format'  => 'uri',
                    'context' => [ 'view' ],
                ],
                'somedate'          => [
                    'type'    => 'string',
                    'format'  => 'date-time',
                    'context' => [ 'view' ],
                ],
                'someemail'         => [
                    'type'    => 'string',
                    'format'  => 'email',
                    'context' => [ 'view' ],
                ],
                'somehex'           => [
                    'type'    => 'string',
                    'format'  => 'hex-color',
                    'context' => [ 'view' ],
                ],
                'someuuid'          => [
                    'type'    => 'string',
                    'format'  => 'uuid',
                    'context' => [ 'view' ],
                ],
                'sometextfield'     => [
                    'type'    => 'string',
                    'format'  => 'text-field',
                    'context' => [ 'view' ],
                ],
                'sometextareafield' => [
                    'type'    => 'string',
                    'format'  => 'textarea-field',
                    'context' => [ 'view' ],
                ],
                'someenum'          => [
                    'type'    => 'string',
                    'enum'    => [ 'a', 'b', 'c' ],
                    'context' => [ 'view' ],
                ],
                'someargoptions'    => [
                    'type'        => 'integer',
                    'required'    => true,
                    'arg_options' => [
                        'required'          => false,
                        'sanitize_callback' => '__return_true',
                    ],
                ],
                'somedefault'       => [
                    'type'    => 'string',
                    'enum'    => [ 'a', 'b', 'c' ],
                    'context' => [ 'view' ],
                    'default' => 'a',
                ],
                'somearray'         => [
                    'type'        => 'array',
                    'items'       => [
                        'type' => 'string',
                    ],
                    'minItems'    => 1,
                    'maxItems'    => 10,
                    'uniqueItems' => true,
                    'context'     => [ 'view' ],
                ],
                'someobject'        => [
                    'type'                 => 'object',
                    'additionalProperties' => [
                        'type' => 'string',
                    ],
                    'properties'           => [
                        'object_id' => [
                            'type' => 'integer',
                        ],
                    ],
                    'patternProperties'    => [
                        '[0-9]' => [
                            'type' => 'string',
                        ],
                    ],
                    'minProperties'        => 1,
                    'maxProperties'        => 10,
                    'anyOf'                => [
                        [
                            'properties' => [
                                'object_id' => [
                                    'type'    => 'integer',
                                    'minimum' => 100,
                                ],
                            ],
                        ],
                        [
                            'properties' => [
                                'object_id' => [
                                    'type'    => 'integer',
                                    'maximum' => 100,
                                ],
                            ],
                        ],
                    ],
                    'oneOf'                => [
                        [
                            'properties' => [
                                'object_id' => [
                                    'type'    => 'integer',
                                    'minimum' => 100,
                                ],
                            ],
                        ],
                        [
                            'properties' => [
                                'object_id' => [
                                    'type'    => 'integer',
                                    'maximum' => 100,
                                ],
                            ],
                        ],
                    ],
                    'ignored_prop'         => 'ignored_prop',
                    'context'              => [ 'view' ],
                ],
            ],
        ];

        return $this->add_additional_fields_schema($schema);
    }
}
