<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Biloba\ManufacturerPro\Core\Content\Cms\Events\BilobaProductResultListCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BilobaManufacturerDetailSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BilobaProductResultListCriteriaEvent::class => ['onBilobaProductResultListCriteria', 90],
        ];
    }

    public function onBilobaProductResultListCriteria(BilobaProductResultListCriteriaEvent $event): void
    {
        dd('BilobaProductResultListCriteriaEvent fired');

        $criteria = $event->getCriteria();
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('options.group');
    }
}