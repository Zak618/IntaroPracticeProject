<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Filter\Store\ProductGroupFilterType;
use RetailCrm\Api\Model\Request\Store\ProductGroupsRequest;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BaseController extends AbstractController
{
    protected $client;

    public function __construct(
        HttpClientInterface $client
    ) {
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
        // TODO данные должны быть доступны после авторизации
        // удалить
        $user = $this->getUser();
        
        if(!is_null($user))
            $user->crmLoad();
        
        $cache = new FilesystemAdapter();

        $category = $cache->getItem('category_menu');

        if (!$category->isHit()) 
        {
            $client = $this->createRetailCrmClient();

            $request = new ProductGroupsRequest();
            $request->filter = new ProductGroupFilterType();

            try {            
                $categoryMenu = ($client->store->productGroups($request))->productGroup;
            } catch (Exception $exception) {
                dd($exception);
                exit(-1);
            }

            // TODO временно
            foreach($categoryMenu as $key => $c)
            {
                if($c->parentId !== null)
                {
                    unset($categoryMenu[$key]);
                }
            }

            $category->set($categoryMenu);
            $category->expiresAfter(3600 * 2);
            $cache->save($category);
        }

        return [
            'logo' => $_ENV['LOGO_SRC'],
            'shopName' => $_ENV['SHOP_NAME'],
            'category_menu' => $cache->getItem('category_menu')->get()
        ];
    }
}
