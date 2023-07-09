<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\RegistrationFormType;
use App\Security\ClientAuthenticator;
use App\Security\EmailVerifier;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Request\Customers\CustomersCreateRequest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends BaseController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, ClientAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Client();
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setEmail($form->get('email')->getData());
            $user->setUuid(uuid_create(UUID_TYPE_RANDOM));

            $entityManager->persist($user);
            $entityManager->flush();

            $client = $this->createRetailCrmClient();

            // заготовка для отправления запроса
            $requestCustomer = new CustomersCreateRequest();
            $requestCustomer->customer = new Customer();

            // определение полей пользователя
            // докю: https://github.com/retailcrm/api-client-php/blob/fa0e8a7075aa0b72b87f5632af01f2b000a61f6e/doc/index.md
            $requestCustomer->customer->externalId = (string)$user->getUuid();
            $requestCustomer->customer->email  = $form->get('email')->getData();
            $requestCustomer->customer->firstName = $form->get('firstname')->getData();
            $requestCustomer->customer->lastName = $form->get('lastname')->getData();
            $requestCustomer->customer->patronymic = $form->get('patronymic')->getData();
            $requestCustomer->customer->phones = [new CustomerPhone()];
            $requestCustomer->customer->phones[0]->number = $form->get('phone')->getData();
            $requestCustomer->customer->birthday = $form->get('birthday')->getData();
            $requestCustomer->customer->sex = $request->get('sex') == 2 ? 'female' : 'male';
            
            
            // TODO дописать
            // $requestCustomer->customer->address = $form->get('address');

            try {
                $response = $client->customers->create($requestCustomer);
                // dd($response);
            } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
                // удаляет пользователя
                $entityManager->remove($user);
                dd($exception);
                exit(-1);
            }

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
