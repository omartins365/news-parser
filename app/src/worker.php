<?php

use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpKernel\KernelInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

require __DIR__.'/../vendor/autoload.php';

// Load Symfony kernel
$kernel = new AppKernel('prod', false);
$kernel->boot();
$container = $kernel->getContainer();

// Create consumer
$consumer = $container->get('old_sound_rabbit_mq.news_consumer_consumer');
$consumer->setContainer($container);
$consumer->setLogger($container->get('logger'));

// Consume messages from the queue
$consumer->consume(1);

// Exit script
$kernel->shutdown();
