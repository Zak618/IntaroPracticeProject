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
        
        return $client;
    }

    protected function getHeader()
    { 
        $cache = new FilesystemAdapter();

        $header = $cache->getItem('headerinfo');
        if (!$header->isHit()) {
            $client = $this->createRetailCrmClient();

            $request = new ProductGroupsRequest();
            $request->filter = new ProductGroupFilterType();

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

            // сохраняем данные в кеш
            $header->set([
                'logo' => $_ENV['LOGO_SRC'],
                'shopName' => $_ENV['SHOP_NAME'],
                'category_menu' => array_values($category)
            ]);
            $header->expiresAfter(3600 * 2);
            $cache->save($header);
        }

        return $cache->getItem('headerinfo')->get();
    }
}
