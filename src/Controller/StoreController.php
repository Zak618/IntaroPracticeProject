<?php

namespace App\Controller;

use Exception;
use RetailCrm\Api\Model\Filter\Store\ProductFilterType;
use RetailCrm\Api\Model\Filter\Store\ProductGroupFilterType;
use RetailCrm\Api\Model\Request\Store\ProductGroupsRequest;
use RetailCrm\Api\Model\Request\Store\ProductsRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends BaseController
{
    #[Route('', name: 'app_main')]
    public function main(): Response
    {
        $header = $this->getHeader();
        
        return $this->render('main.html.twig', [
            'header' => $header
        ]);
    }
    
    #[Route('/catalog/', name: 'app_store')]
    public function index(Request $request): Response
    {
        $header = $this->getHeader();

        // получаем первые 20 записей
        $client = $this->createRetailCrmClient();

        try {            
            $response = ($client->store->products());
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }
        
        return $this->render('store/category.html.twig', [
            'header' => $header, 
            'categories' => $header['category_menu'],
            'products' => $response->products,
            'totalPageCount' => 0
        ]);
    }

    // страница раздела
    #[Route('/catalog/{id}', name: 'app_category_page')]
    public function page(Request $request): Response
    {
        $currentPage = $request->query->getInt('page', 1);

        $client = $this->createRetailCrmClient();

        $requestProducts = new ProductsRequest();
        $requestProducts->filter = new ProductFilterType();
        $requestProducts->filter->groups = [$request->get('id')];
        $requestProducts->page = $currentPage;

        try {            
            $response = ($client->store->products($requestProducts));
            $cat = $this->getChildCategoryById($request->get('id'));

        } catch (Exception $exception) {
            return $this->render('store/category.html.twig', [
                'header' => $this->getHeader(),
                'error' => 'Произошла ошибка, возможно, раздела не существует.',
                'totalPageCount' => 0,
                'categories' => [],
                'products' => []
            ]);
        }
        
        return $this->render('store/category.html.twig', [
            'header' => $this->getHeader(),
            'categories' => $cat['categoties'],
            'title' => $cat['name'],
            'products' => $response->products,
            'totalPageCount' => $response->pagination->totalPageCount
        ]);
    }

    // детальная страница товара
    #[Route('/product/{id}', name: 'app_product', methods: ['GET'])]
    public function detailPage(Request $request): Response
    {
        $client = $this->createRetailCrmClient();

        $requestProduct = new ProductsRequest();
        $requestProduct->filter = new ProductFilterType();
        $requestProduct->filter->ids = [$request->get('id')];

        try {            
            $response = $client->store->products($requestProduct);
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }

        return $this->render('store/product.html.twig', [
            'header' => $this->getHeader(),
            'product' => empty($response->products) ? null : $response->products[0]
        ]);
    }

    private function getChildCategoryById($categoryId)
    {
        $client = $this->createRetailCrmClient();

        $request = new ProductGroupsRequest();
        $request->filter = new ProductGroupFilterType();
        $request->filter->parentGroupId = $categoryId;

        $category = ($client->store->productGroups($request))->productGroup;
        
    
        $request = new ProductGroupsRequest();
        $request->filter = new ProductGroupFilterType();
        $request->filter->ids = [$categoryId];

        $categoryParent = ($client->store->productGroups($request))->productGroup[0]->name;

        return [
            'categoties' => $category, 
            'name' => $categoryParent
        ];
    }
}
