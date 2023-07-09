<?php 

namespace App\EventListener;

use App\Entity\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class ClientLoginSubscriber implements EventSubscriberInterface
{
    public function onUserLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        if ($user instanceof Client) {
            $user->crmLoad();
        }

        dd($user);
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onUserLogin',
        ];
    }
}
