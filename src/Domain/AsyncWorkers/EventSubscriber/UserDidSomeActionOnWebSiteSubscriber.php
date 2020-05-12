<?php

namespace App\Domain\AsyncWorkers\EventSubscriber;

use App\Domain\AsyncWorkers\Event\UserDidSomeActionOnWebSite;
use SocialTech\StorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * for debugging you may use:
 * php bin/console debug:event-dispatcher
 */
class UserDidSomeActionOnWebSiteSubscriber implements EventSubscriberInterface
{
    /** @var StorageInterface */
    private StorageInterface $storage;

    /** @var ParameterBagInterface */
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag, StorageInterface $storage)
    {
        $this->storage = $storage;
        $this->parameterBag = $parameterBag;
    }


    /**
     * return the subscribed events, their methods and priorities
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserDidSomeActionOnWebSite::class => [
                ['storeAnalyticsData', 10],
            ],
        ];
    }

    /**
     * @param UserDidSomeActionOnWebSite $event
     */
    public function storeAnalyticsData(UserDidSomeActionOnWebSite $event): void
    {
        $name = $event->getId() . '.json';
        $this->storage->store($this->parameterBag->get('path_for_analytics') . '/' . $name, $event->asJson());
    }
}
