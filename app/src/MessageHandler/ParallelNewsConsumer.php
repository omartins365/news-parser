<?php

namespace App\MessageHandler;

use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Process\Process;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

class ParallelNewsConsumer implements ConsumerInterface
{
    private $maxProcesses;

    public function __construct(int $maxProcesses)
    {
        $this->maxProcesses = $maxProcesses;
    }

    public function execute(AMQPMessage $message)
    {
        $newsId = $message->getBody();
        $command = sprintf('php bin/console app:process-news %d', $newsId);

        // Create a new process for each news item
        $processes = [];
        for ($i = 1; $i <= $this->maxProcesses; $i++) {
            $process = new Process(explode(' ', $command));
            $process->start();
            $processes[] = $process;
        }

        // Wait for all processes to finish
        foreach ($processes as $process) {
            $process->wait();
        }
    }

    
    public function consume(AMQPMessage $msg)
    {
        try {
            $this->execute($msg);
            return ConsumerInterface::MSG_ACK;
        } catch (\Exception $e) {
            // Log the error
            return ConsumerInterface::MSG_REJECT_REQUEUE;
        }
    }
}
