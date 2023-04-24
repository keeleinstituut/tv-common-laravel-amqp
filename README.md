# tv-common-laravel-amqp

The simple lib for publishing/consuming messages using AMQP protocol (RabbitMQ).

## Install

Add the next code into `composer.json`

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/keeleinstituut/tv-common-laravel-amqp"
    }
  ],
  "require": {
    "keeleinstituut/tv-common-laravel-amqp": "*"
  },
  "minimum-stability": "dev"
}
```

## Configuration

```bash
# Publish config file
php artisan vendor:publish --provider="Amqp\AmqpServiceProvider"
```

### Parameters

The next params should be defined in `.env` config:
- AMQP_HOST
- AMQP_PORT
- AMQP_USER
- AMQP_PASSWORD
- AMQP_VHOST

The `amqp.php` config file contains 2 additional sections:
- publisher
- consumer

You can skip one of them if the app only publish/consume the messages.

#### Publisher
The publisher section contains list of exchanges that will be used by the app. Please note all exchanges that will be used should be declared in `publisher.exchanges` section.

For each exchange you have to define the next params:
- exchange - the name of exchange
- type - one of 'fanout', 'topic', 'direct'.

Other parameters have their default values and can be skipped. The full params list with default values:
```php
return [
    'publisher' => [
        'exchanges' => [
            [
                'exchange' => '',
                'type' => '',
                'passive' => false,
                'durable' => true,
                'auto_delete' => false,
                'internal' => false,
                'nowait' => false,
                'properties' => [],
            ]
        ],
    ],
];
```

Please note that creation of exchanges should be done inside the app that publish messages, creation of queues and bindings should be done inside the app that consume messages.

#### Consumer
The consumer configuration contains two sections:
- queues
- events

**Queues**

In this section you have to specify the list of queues and their bindings that will be used by the app. 
Please note queues won't be created automatically if they are not declared in `consumer.queues` section.

For each queue you have to define the next params:
- queue - the name of queue
- bindings - the list of bindings to exchanges:
  - exchange - the name of exchange
  - routingKey - routing key (not mandatory in case `fanout`) 

Other parameters have their default values and can be skipped. The full params list with default values:
```php
return [
    'consumer' => [
        'queues' => [
            [
                'queue' => 'some-queue',
                'passive' => false,
                'durable' => true,
                'exclusive' => false,
                'auto_delete' => false,
                'nowait' => false,
                'properties' => [],
                'bindings' => [
                    [
                        'exchange' => 'some-exchange',
                        'routingKey' => 'some-key',
                    ]
                ]
            ]
        ],
        'events' => [
            'mode' => MessageEventFactory::MODE_QUEUE,
            'map' => [
            
            ]
        ],
    ],
];
```

**Events**

The library works in the way that after consuming of the message it produce a [Laravel event](https://laravel.com/docs/10.x/events) based on received message.
To make the decision about what event should be triggered the `events` config section is used.

It contains two keys:
- `mode` - the param based on which the app will pick the corresponding event. Allowed values:
  - `queue` - based on the queue name
  - `routing-key` - based on the message routing key.
  - `static` - will use the same event for all messages.
- `map` - the associated array that contains param value as a key (routing key/queue name/just string 'default' depends on the selected mode). The value should be the class name that extends `Amqp\Events\BaseConsumedEvent`.


## Usage

The lib contains two singletons that can be easily injected:
- `Amqp\Publisher`
- `Amqp\Consumer`

### Publish message

```php
$publisher = app()->make(Amqp\Publisher);

$publisher->publish(['key' => 'value'], 'name-of-exchange');
$publisher->publish($objectThatImplementsJsonSerializable, 'name-of-exchange', 'name-of-routing-key');
$publisher->publish($objectThatImplementsJsonable, 'name-of-exchange', 'name-of-routing-key', [
    'custom-header-name' => 'header-value'
]);
```

### Consuming messages

The lib contains command for consuming messages

To start consuming messages run the next command in terminal
```bash
php artisan amqp:consume name-of-queue
```

To init all exchanges, bindings, queues run:
```bash
php artisan amqp:setup
```

It's not mandatory because exchanges, bindings, and queues will be created automatically when you will interact with them.


## Tests
```bash
composer test
```