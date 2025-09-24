<?php

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class PageLoader
{

    public function __construct(private readonly string $cacheDir) {}
    public function getPage(int $year, int $week)
    {
        $dir = $this->cacheDir . '/cached_pages/' . $year;
        $file = $dir . '/' . $week . '.html';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        $url = 'https://wol.jw.org/uk/wol/meetings/r15/lp-k/' . $year . '/' . $week;

        $content =  file_get_contents($url);

        $crawler = new Crawler($content);
        $href = $crawler->filter('#materialNav a')->first()->attr('href');
        $url = 'https://wol.jw.org' . $href;
        $content =  file_get_contents($url);
        file_put_contents($file, $content);
        return $content;
    }
}
