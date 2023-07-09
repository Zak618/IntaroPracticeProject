<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use RetailCrm\Api\Enum\ByIdentifier;


use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Request\Customers\CustomersCreateRequest;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;

use RetailCrm\Api\Model\Entity\Customers\CustomerAddress;
use RetailCrm\Api\Model\Entity\CustomersCorporate\CustomerCorporate;
use RetailCrm\Api\Model\Response\Customers\CustomersEditResponse;
use RetailCrm\Api\Model\Request\Customers\CustomersEditRequest;

#[Route('/client')]
class ClientController extends BaseController
{
    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ClientRepository $clientRepository): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clientRepository->save($client, true);

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }


    #[Route('/{id}/edit_client', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit_client(Request $request, Client $client, ClientRepository $clientRepository): Response
    {

        $user_api =$this->createRetailCrmClient();
        $user=$this->getUser();
        $user->crmLoad();
        $form = $this->createForm(ClientType::class, $user);
        //dd($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // заготовка для отправления запроса
            $requestCustomer = new CustomersEditRequest();
            $requestCustomer->customer= new Customer();
            // $requestCustomer->by= ByIdentifier::ID;
            $requestCustomer->customer->email  = $form->get('email')->getData();
            $requestCustomer->customer->firstName = $form->get('firstname')->getData();
            $requestCustomer->customer->lastName = $form->get('lastname')->getData();
            $requestCustomer->customer->patronymic = $form->get('patronymic')->getData();
            $requestCustomer->customer->phones = [new CustomerPhone()];
            $requestCustomer->customer->phones[0]->number = $form->get('phone')->getData();
            $requestCustomer->customer->birthday = $form->get('birthday')->getData();
            $requestCustomer->customer->sex = $request->get('sex') == 2 ? 'female' : 'male';
        // $requestCustomer->customer->address = new CustomerAddress();
            // $requestCustomer->customer->address['text'] = $form-> get('address')->getData();
            
            
            try {
                $user_api->customers->edit($user->getUuid(), $requestCustomer);
            } catch (Exception $e) {
                dd($e);
            }

            $clientRepository->save($client, true);

            return $this->redirectToRoute('app_store', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, ClientRepository $clientRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $clientRepository->remove($client, true);
        }

        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }
}
