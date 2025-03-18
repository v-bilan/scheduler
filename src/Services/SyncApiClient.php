<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SyncApiClient
{
    const API_HOST = 'http://127.0.0.1:8000/api/v1/';

    public function __construct(private HttpClientInterface $httpClient) {}

    public function getItems($type)
    {
        $url = self::API_HOST . $type;

        do {
            $response = $this->httpClient->request('GET', $url);

            if ($response->getStatusCode() != 200) {
                dd('some errror');
            }
            $dataArray =  $response->toArray();

            foreach ($dataArray['data'] ?? [] as $item) {
                yield $item;
            }

            $url = $dataArray['links']['next'] ?? null;
        } while ($url);
    }
}
