<?php
namespace App\Command;

use App\MessageHandler\NewsConsumer;
use Symfony\Component\Console\Command\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

class NewsConsumerCommand extends Command
{
    private $consumer;

    public function __construct(NewsConsumer $consumer)
    {
        parent::__construct();
        $this->consumer = $consumer;
    }

    protected function configure()
    {
        $this->setName('app:news:consume')
             ->setDescription('Consume news from RabbitMQ queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest', '/');
        $channel = $connection->channel();

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('news_queue', '', false, false, false, false, [$this->consumer, 'execute']);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
