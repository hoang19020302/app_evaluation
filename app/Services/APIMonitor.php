<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class APIMonitor
{
    public function checkAPI($url)
    {
        $client = new Client();

        try {
            $response = $client->get($url);
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                return true;
            } else {
                return false;
            }
        } catch (GuzzleException $e) {
            return false;
        }
    }
}
