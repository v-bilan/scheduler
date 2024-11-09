<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;
use App\Services\PageLoader;
class TasksParser
{
    public function __construct(private PageLoader $pageLoader)
    {

    }
    public function getTasks(int $year = 2024, int $week =29)
    {

        $content = $this->pageLoader->getPage($year, $week);
        $crawler = new Crawler($content);
        $result['date'] = $crawler->filter('h1')->first()->text();
        $result['tasks'] = [];
        $crawler->filter('h3')->each(function (Crawler $el) use(&$result) {
            $matches = [];

            if (preg_match('/\d{1,2}\.\s/', $el->text(), $matches)) {
                $speech = $el->nextAll()->count() > 0 && str_contains($el->nextAll()->eq(0)?->text(), 'Промова.');
                    $result['tasks'][] =  $el->text() . ( $speech ? ' '. 'Промова.': '');
            } ;
        });
        return $result;
    }
}