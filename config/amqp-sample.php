<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AMQP connection properties
    |--------------------------------------------------------------------------
    */
    'connection' => [
        'host' => env('AMQP_HOST', 'localhost'),
        'port' => env('AMQP_PORT', 5672),
        'username' => env('AMQP_USER', ''),
        'password' => env('AMQP_PASSWORD', ''),
        'vhost' => env('AMQP_VHOST', '/'),
        'connect_options' => [],
        'ssl_options' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | AMQP publisher properties (remove if not needed)
    |--------------------------------------------------------------------------
    */
    'publisher' => [
        'exchanges' => [
            [
                'exchange' => 'amq.topic',
                'type' => 'topic',
                'passive' => false,
                'durable' => true,
                'auto_delete' => false,
                'internal' => false,
                'nowait' => false,
                'properties' => [],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AMQP consumer properties (remove if not needed)
    |--------------------------------------------------------------------------
    */
    'consumer' => [
        'queues' => [
            [
                'queue' => 'some-queue',
                'bindings' => [
                    [
                        'exchange' => 'some-exchange',
                        'routingKey' => 'some-key',
                    ],
                ],
            ],
        ],
        'events' => [
            'mode' => 'routing-key',
            'map' => [
                'institution.created' => 'App\Events\SomeEventClassNameThatExtends_BaseConsumedEvent',
            ],
        ],

        'no_local' => false, // Don't receive messages published by this consumer.
        'no_ack' => false, // Tells the server if the consumer will acknowledge the messages.
        'exclusive' => false, // Request exclusive consumer access, meaning only this consumer can access the queue
        'nowait' => false,
        'properties' => [],
        'timeout' => 0,

        /*
         * The consumer-prefetch make it possible to limit the number of unacknowledged messages on a channel (or connection) when consuming.
         * Or, in other words, don't dispatch a new message to a worker until it has processed and acknowledged the previous one
         */
        'qos' => [
            'prefetch_size' => 0,
            'prefetch_count' => 1,
            'global' => false,
        ],
    ],
];
