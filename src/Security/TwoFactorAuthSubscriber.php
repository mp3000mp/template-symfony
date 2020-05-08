<?php declare(strict_types=1);

namespace App\Security;

use App\Service\OTP\OTPService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * On redirige vers le formulaire de double auth si pas déjà fait
 * Class DoubleAuthSubscriber
 *
 * @package App\EventSubscriber
 */
class TwoFactorAuthSubscriber implements EventSubscriberInterface
{

    private const FIREWALL_NAME = 'main';

    /** @var RouterInterface  */
    private $router;
    /** @var TokenStorageInterface  */
    private $tokenStorage;
    /** @var OTPService */
    private $OTPService;

    /**
     * DoubleAuthSubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param OTPService $OTPService
     */
    public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router, OTPService $OTPService)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->OTPService = $OTPService;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', -10]],
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if($event->getRequest()->attributes->get('_route') === OTPService::ROUTE_TWO_FACTOR){
            return;
        }

        $currentToken = $this->tokenStorage->getToken();
        if($currentToken instanceof PostAuthenticationGuardToken
            && $currentToken->getProviderKey() === self::FIREWALL_NAME
            && !in_array(OTPService::ROLE_TWO_FACTOR_SUCCEED, $currentToken->getRoleNames(),true)
        ){
            if($currentToken->getUser()->getTwoFactorSecret() === null){
                $this->OTPService->addTwoFactorRole($this->tokenStorage, $event->getRequest()->getSession());
            }else{
                $response = new RedirectResponse($this->router->generate(OTPService::ROUTE_TWO_FACTOR));
                $event->setResponse($response);
            }
        }
    }
}
