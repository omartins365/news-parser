<?php

namespace App\Command;

use Carbon\Carbon;
use App\Entity\News;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\Console\Command\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewsConsumerCommand extends Command
{
    private $producer;
    private $connection;

    private $channel;

    public function __construct(Producer $producer)
    {
        parent::__construct();
        $this->producer = $producer;
        $amqpUrl = "amqp://rabbitmq_user:rabbitmq_password@news-parser-rabbitmq-1:5672/";
        $amqpUrlParts = parse_url($amqpUrl);
        $connection = new AMQPStreamConnection(
            $amqpUrlParts['host'],
            $amqpUrlParts['port'],
            $amqpUrlParts['user'],
            $amqpUrlParts['pass']
        );
        $this->channel = $connection->channel();

        $this->connection = $connection;
    }

    protected function configure()
    {
        $this->setName('app:news:publish')
            ->setDescription('Publish news to RabbitMQ exchange');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Create a new channel
        $channel = $this->channel;

        // Declare the news queue and exchange
        $channel->queue_declare('news_queue', false, true, false, false);
        $channel->exchange_declare('news_exchange', 'direct', false, true, false);
        $channel->queue_bind('news_queue', 'news_exchange', 'news_routing_key');

        // Set the channel to prefetch one message at a time
        $channel->basic_qos(null, 1, null);


        // Get news from a source (e.g. RSS feed)
        $news = $this->getNewsFromSource();

        // Publish news to RabbitMQ exchange
        foreach ($news as $item) {
            $this->producer->publish(json_encode($item), 'news_routing_key');
        }
    }

    private function getNewsFromSource()
    {
        // Get news from a source (e.g. RSS feed)
        // ...
        mb_internal_encoding("UTF-8");
        // Create a HTTP client
        $client = new Client();

        // Fetch the HTML content of the website
        $response = $client->request('GET', 'https://highload.today/');
        $html = (string) $response->getBody();

        // Create a crawler to parse the HTML
        $crawler = new Crawler($html);

        $news =  $crawler->filter('.lenta-item')->each(function ($node, $i) {
            $title = $node->filter('a > h2')->text();
            $description = $node->filter('.lenta-item > p')->text();
            // dd($description);
            $picture = $node->filter('div.lenta-image > img.wp-post-image')->attr('data-lazy-src');
            // dd($picture);
            $carbon = new Carbon();
            $timestr = $node->filter('.meta-datetime')->text();
            // dump($timestr);
            // $timestr = iconv('UTF-8', 'Windows-1251', $timestr);
            $timestr = str_replace("назад", "ago", $timestr);
            // dump($timestr);
            $timestr = mb_convert_encoding($timestr, 'UTF-8', 'auto');
            $date = Carbon::parseFromLocale(($timestr), 'ru');




            // Create a new News object and set its properties
            $newsItem = new News();
            $newsItem->setTitle($title);
            $newsItem->setDescription($description);
            $newsItem->setPicture($picture);
            $newsItem->setDate($date);
            $newsItem->setUpdatedAt($date);

            // $this->entityManager->persist($newsItem);
            return $newsItem;
            // Add the News object to the list of news to save to the database\
        });


        return $news;
    }
}
