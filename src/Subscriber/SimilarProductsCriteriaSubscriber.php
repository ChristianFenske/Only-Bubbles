<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use SyseaSimilarProducts\Event\SimilarProductsCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SimilarProductsCriteriaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SimilarProductsCriteriaEvent::class => ['onSimilarProductsCriteria', 90],
        ];
    }

    public function onSimilarProductsCriteria(SimilarProductsCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();
        $criteria->addAssociation('manufacturer');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('options.group');
    }
}
