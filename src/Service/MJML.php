<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MJML
{
    private $client;

    public function __construct()
    {
        $this->client = HttpClient::createForBaseUri('https://api.mjml.io/v1', [
            'auth_basic' => [getenv('MJML_PUBLIC_KEY'), getenv('MJML_SECRET_KEY')],
        ]);
    }

    public function getHtml(string $mjml): string
    {
        $html = '';

        try {
            $response = $this->client->request('POST', 'https://api.mjml.io/v1/render', ['json' => ['mjml' => $mjml]]);

            if (200 === $response->getStatusCode()) {
                $response_body_array = $response->toArray();
                $html = $response_body_array['html'];
            }
        } catch (TransportExceptionInterface $e) {
        }

        return $html;
    }
}
