<?php

namespace MauticPlugin\MauticJobsRssBundle\EventListener;

use DOMDocument;
use DOMXPath;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailSendEvent;
use SimpleXMLElement;

class EmailSubscriber extends CommonSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            EmailEvents::EMAIL_ON_SEND    => array('onEmailGenerate', 10000),
            EmailEvents::EMAIL_ON_DISPLAY => array('onEmailGenerate', 10000)
        );
    }

    public function onEmailGenerate(EmailSendEvent $event){

        try {
            $dom = new DomDocument();

            $dom->loadHTML($event->getContent());

            $finder = new DomXPath($dom);

            $cl_job_item = 'rss-job-item';
            $cl_title = "rss-title";
            $cl_description = "rss-description";

            $item_job = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $cl_job_item ')]");
            $count_jobs = $item_job->length;

            if(!empty($count_jobs)){
                $xml_feed_url = $item_job->item(0)->getAttribute('data-rss-url');
                $xml_feed_data = $this->getFeedData($xml_feed_url);

                for ($i = 0; $i < $count_jobs; $i++){

                    $item_from_rss_feed = current($xml_feed_data);

                    $rss_title = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $cl_title ')]")->item($i);
                    $rss_title->textContent = $item_from_rss_feed->title;
                    $rss_title->setAttribute('href', $item_from_rss_feed->link);

                    $rss_description = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $cl_description ')]")->item($i);
                    $rss_description->textContent = strip_tags($item_from_rss_feed->description);

                    next($xml_feed_data);
                }
            }

            $event->setContent($finder->document->saveHTML());

        } catch (\Exception $e){

        }
    }

    /**
     * @param $xml_feed_url
     * @return array
     */
    private function getFeedData($xml_feed_url)
    {
        $xml_feed_url = str_replace(' ','', $xml_feed_url);

        $content = file_get_contents($xml_feed_url);

        if(strpos($content, '</channel>') === false){
            $content .= '</channel></rss>';
        }

        try {
            $feed = new SimpleXMLElement($content);
            $result = ((array)$feed->channel)['item'];
        } catch (\Exception $e){
            $result = [];
        }

        return $result;
    }
}