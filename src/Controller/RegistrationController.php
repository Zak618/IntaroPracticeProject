<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\RegistrationFormType;
use App\Security\ClientAuthenticator;
use App\Security\EmailVerifier;

use Doctrine\ORM\EntityManagerInterface;

use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Request\Customers\CustomersCreateRequest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends BaseController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, ClientAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Client();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $client = $this->createRetailCrmClient();

            // заготовка для отправления запроса
            $request = new CustomersCreateRequest();
            $request->customer = new Customer();

            // определение полей пользователя
            // пример https://packagist.org/packages/retailcrm/api-client-php
            $request->site = 'test';
            $request->customer->externalId = (string)$user->getId();
            $request->customer->email = $form->get('email')->getData();
            $request->customer->firstName = $form->get('firstname')->getData();
            $request->customer->lastName = $form->get('lastname')->getData();
            $request->customer->patronymic = $form->get('patronymic')->getData();
            $request->customer->phones = [new CustomerPhone()];
            $request->customer->phones[0]->number = $form->get('phone')->getData();
            $request->customer->birthday = $form->get('birthday')->getData();

            try {
                $response = $client->customers->create($request);
                dd($response);
            } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
                echo $exception; // Every ApiExceptionInterface instance should implement __toString() method.
                $entityManager->remove($user);
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
