<?php

namespace App\Controller;

use Exception;
use RetailCrm\Api\Model\Filter\Store\ProductFilterType;
use RetailCrm\Api\Model\Request\Store\ProductsRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends BaseController
{
    #[Route('', name: 'app_store')]
    public function index(): Response
    {
        return $this->render('store/index.html.twig', [
            'header' => $this->getHeader()
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_page')]
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
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }
        
        return $this->render('store/category.html.twig', [
            'header' => $this->getHeader(),
            'products' => $response->products,
            'totalPageCount' => $response->pagination->totalPageCount
        ]);
    }

    #[Route('/product/{id}', name: 'app_product', methods: ['POST'])]
    public function addProductToCart(Request $request)
    {
        $client = $this->createRetailCrmClient();

        $requestProduct = new ProductsRequest();
        $requestProduct->filter = new ProductFilterType();
        $requestProduct->filter->ids = [$request->get('id')];

        try {            
            $product = ($client->store->products($requestProduct))->products[0];
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }

        // todo доделать 

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
            $response = ($client->store->products($requestProduct))->products[0];
            // dd($response);
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }

        return $this->render('store/product.html.twig', [
            'header' => $this->getHeader(),
            'product' => $response
        ]);
    }
}
