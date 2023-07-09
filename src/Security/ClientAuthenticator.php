<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class ClientAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        
        $email = $request->request->get('email', '');
        // $phone = $request->request->get('phone', '');
        // $birthdate = $request->request->get('birthdate', '');
        // $gender = $request->request->get('gender', '');


        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ],
            // [
            //     'phone' => $phone,
            //     'birthdate' => $birthdate,
            //     'gender' => $gender,
            // ]

        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();
        $user->crmLoad();

/*         // Получите пользователя, прошедшего аутентификацию
        $user = $token->getUser();

        // Получите данные о телефоне, дате рождения и поле из аутентификационного паспорта
        $phone = $token->getAttributes()['phone'] ?? null;
        $birthdate = $token->getAttributes()['birthdate'] ?? null;
        $gender = $token->getAttributes()['gender'] ?? null;

        // Обновите значения полей в объекте пользователя
        $user->setPhone($phone);
        $user->setBirthdate($birthdate);
        $user->setGender($gender);

        // Сохраните изменения в базе данных

        // Добавьте код для переадресации на главную страницу
        return new RedirectResponse($this->urlGenerator->generate('home')); */

    
        // For example:
         return new RedirectResponse($this->urlGenerator->generate('app_store'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
