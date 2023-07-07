<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Filter\Store\ProductGroupFilterType;
use RetailCrm\Api\Model\Request\Store\ProductGroupsRequest;
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
        $client->api->credentials();
        
        return $client;
    }

    protected function getHeader()
    { 
        $client = $this->createRetailCrmClient();

        $request = new ProductGroupsRequest();
        $request->filter = new ProductGroupFilterType();

        // TODO указать нулл уровень и убрать перебор с удалением
        // $request->filter->parentGroupId = 19;
        
        // $request->page = 1;
        // $request->limit = 10;

        try {            
            $category = ($client->store->productGroups($request))->productGroup;
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }

        // TODO временно
        foreach($category as $key => $c)
        {
            if($c->parentId !== null)
            {
                unset($category[$key]);
            }
        }
        
        return  [
            'logo' => $_ENV['LOGO_SRC'],
            'shopName' => $_ENV['SHOP_NAME'],
            'category_menu' => array_values($category)
        ];
    }
}
