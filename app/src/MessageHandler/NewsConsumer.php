<?php

namespace App\MessageHandler;

use App\Entity\News;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

class NewsConsumer implements ConsumerInterface
{
    private $entityManager;
    private $container;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function execute(AMQPMessage $msg)
    {
        $newsMessage = unserialize($msg->body);

        $title = $newsMessage->getTitle();
        $description = $newsMessage->getDescription();
        $picture = $newsMessage->getPicture();
        $date = $newsMessage->getDate();
        
        dump($title);
        // Check if the title already exists in the database
        $existingNews = $this->entityManager->getRepository(News::class)->findOneBy(['title' => $title]);
        if ($existingNews) {
            // Update the updated_at column with the current date and time
            $existingNews->setUpdatedAt($date);
            $this->entityManager->flush();
            return;
        }
        // dd($news);
        // Save the news to the database
        $newsItem = new News();
        $newsItem->setTitle($title);
        $newsItem->setDescription($description);
        $newsItem->setPicture($picture);
        $newsItem->setDate($date);
        $newsItem->setUpdatedAt($date);

        $this->entityManager->persist($newsItem);
        $this->entityManager->getRepository(News::class);


        $this->entityManager->flush();


        $process = new Process(['php', 'bin/console', 'app:process-news', $news->getId()]);
        $process->start();

        return ConsumerInterface::MSG_ACK;
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
