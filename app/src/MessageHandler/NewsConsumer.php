<?php

namespace App\MessageHandler;

use Carbon\Carbon;
use App\Entity\News;
use GuzzleHttp\Client;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
class NewsConsumer implements ConsumerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute(AMQPMessage $msg)
    {
        mb_internal_encoding("UTF-8");
        $data = json_decode($msg->getBody(), true);

        // Create a HTTP client
        $client = new Client();

        // Fetch the HTML content of the website
        $response = $client->request('GET', 'https://highload.today/');
        $html = (string) $response->getBody();

        // Create a crawler to parse the HTML
        $crawler = new Crawler($html);
        
        $news =  $crawler->filter('.lenta-item')->each(function($node, $i){
            $title = $node->filter('a > h2')->text();
            $description = $node->filter('p')->text();
            $picture = $node->filter('div.lenta-image > img')->attr('src');
            $carbon = new Carbon();
            $timestr = $node->filter('.meta-datetime')->text();
            // dump($timestr);
            // $timestr = iconv('UTF-8', 'Windows-1251', $timestr);
            $timestr = str_replace("назад","ago",$timestr);
            // dump($timestr);
            $timestr = mb_convert_encoding($timestr, 'UTF-8', 'auto');
            $date = Carbon::parseFromLocale(($timestr), 'ru');
            
            dump($title, $date->calendar());
            // Check if the title already exists in the database
            $existingNews = $this->entityManager->getRepository(News::class)->findOneBy(['title' => $title]);
            if ($existingNews) {
                // Update the updated_at column with the current date and time
                $existingNews->setUpdatedAt($date);
                $this->entityManager->flush();
                return;
            }


            // Create a new News object and set its properties
            $newsItem = new News();
            $newsItem->setTitle($title);
            $newsItem->setDescription($description);
            $newsItem->setPicture($picture);
            $newsItem->setDate($date);
            $newsItem->setUpdatedAt($date);
            $this->entityManager->persist($newsItem);
            // return $newsItem;
            // Add the News object to the list of news to save to the database\
        });

        // dd($news);
        // Save the news to the database
        $this->entityManager->getRepository(News::class);

        
        $this->entityManager->flush();
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
