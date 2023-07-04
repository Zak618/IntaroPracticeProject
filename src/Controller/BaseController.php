<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use RetailCrm\Api\Factory\SimpleClientFactory;


class BaseController extends AbstractController
{
    protected function createRetailCrmClient()
    {
        return SimpleClientFactory::createClient($_ENV['RETAIL_CRM_URL'], $_ENV['API_KEY']);
    }
}
