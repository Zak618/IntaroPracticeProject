<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use RetailCrm\Api\Factory\SimpleClientFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BaseController extends AbstractController
{
    protected $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    protected function createRetailCrmClient()
    {
        $client = SimpleClientFactory::createClient($_ENV['RETAIL_CRM_URL'], $_ENV['API_KEY']);
        // $client->api->apiVersions();
        $client->api->credentials();
        return $client;
    }
}
