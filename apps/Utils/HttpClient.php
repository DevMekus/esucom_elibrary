<?php

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpClient
{
    private Client $client;

    public function __construct(string $baseUri = '', array $headers = [])
    {
        $this->client = new Client([
            'base_uri' => $baseUri,
            'headers' => $headers,
            'timeout' => 30,
        ]);
    }

    private function request(string $method, string $uri, array $options = [])
    {
        try {

            $response = $this->client->request($method, $uri, $options);

            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true),
                'raw' => $response->getBody()->getContents()
            ];

        } catch (RequestException $e) {

            if ($e->hasResponse()) {
                return [
                    'status' => $e->getResponse()->getStatusCode(),
                    'error' => $e->getMessage(),
                    'body' => $e->getResponse()->getBody()->getContents()
                ];
            }

            return [
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }

    public function get(string $uri, array $query = [], array $headers = [])
    {
        return $this->request('GET', $uri, [
            'query' => $query,
            'headers' => $headers
        ]);
    }

    public function post(string $uri, array $data = [], array $headers = [])
    {
        return $this->request('POST', $uri, [
            'json' => $data,
            'headers' => $headers
        ]);
    }

    public function put(string $uri, array $data = [], array $headers = [])
    {
        return $this->request('PUT', $uri, [
            'json' => $data,
            'headers' => $headers
        ]);
    }

    public function patch(string $uri, array $data = [], array $headers = [])
    {
        return $this->request('PATCH', $uri, [
            'json' => $data,
            'headers' => $headers
        ]);
    }

    public function delete(string $uri, array $data = [], array $headers = [])
    {
        return $this->request('DELETE', $uri, [
            'json' => $data,
            'headers' => $headers
        ]);
    }
}