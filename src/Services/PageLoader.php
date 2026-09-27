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
        $content = $this->fetchUrl($url);
        //$content =  file_get_contents($url);

        $crawler = new Crawler($content);
        $href = $crawler->filter('#materialNav a')->first()->attr('href');
        $url = 'https://wol.jw.org' . $href;
        $content = $this->fetchUrl($url);
        //$content =  file_get_contents($url);
        file_put_contents($file, $content);
        return $content;
    }

    private function fetchUrl(string $url): ?string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Обов'язковий User-Agent справжнього браузера на macOS, щоб уникнути 403 помилки
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Автоматичне стиснення (gzip)
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        // Додаткові заголовки, які очікує побачити сервер від браузера
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: uk-UA,uk;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control: no-cache',
        ]);

        if (defined('CURL_HTTP_VERSION_2_0')) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        }

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);

        // Перевіряємо, чи не повертає сервер знову помилку
        if ($errno !== 0 || $httpCode !== 200 || $content === false) {
            throw new \RuntimeException("Не вдалося завантажити сторінку (HTTP код: {$httpCode}): {$url}");
        }

        return $content;
    }
}
