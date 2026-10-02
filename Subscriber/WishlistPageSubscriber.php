<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Shopware\Storefront\Page\Wishlist\WishListPageProductCriteriaEvent;
use Shopware\Storefront\Pagelet\Wishlist\GuestWishListPageletProductCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WishlistPageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            WishListPageProductCriteriaEvent::class => 'onWishlistCriteria',
            GuestWishListPageletProductCriteriaEvent::class => 'onGuestWishlistCriteria',
        ];
    }

    public function onWishlistCriteria(WishListPageProductCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('properties.group');
    }

    public function onGuestWishlistCriteria(GuestWishListPageletProductCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('properties.group');
    }
}