<?php


namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function processException(GetResponseForExceptionEvent $event)
    {
        $result = [];
        if($event) {
            if(method_exists($event->getException(), 'getStatusCode')) {
                $code = $event->getException()->getStatusCode();
            } elseif($event->getException()->getCode() === 0) {
                $code = 500;
            } else {
                $code = $event->getException()->getCode();
            }
            $result = [
                'code' => $code,
                'message' => $event->getException()->getMessage()
            ];
        }

        $body = $this->serializer->serialize($result, 'json');

        $event->setResponse(new Response($body, $result['code'], ['Content-Type' => 'application/json']));
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::EXCEPTION => ['processException', 255]
        ];
    }

}