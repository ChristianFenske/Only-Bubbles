<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Shopware\Core\Content\Cms\Events\ProductCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CmsProductCriteriaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductCriteriaEvent::class => 'onCmsProductCriteria',
        ];
    }

    public function onCmsProductCriteria(ProductCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        // load properties + their groups for CMS-loaded products (homepage, shopping experiences)
        $criteria->addAssociation('properties.group');
		$criteria->addAssociation('manufacturer');
    }
}