<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductSearchSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductSearchCriteriaEvent::class => 'onProductSearchCriteria',
            ProductSuggestCriteriaEvent::class => 'onProductSuggestCriteria',
        ];
    }

    public function onProductSearchCriteria(ProductSearchCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('properties.group');
    }

    public function onProductSuggestCriteria(ProductSuggestCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('properties.group');
    }
}