<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Shopware\Core\Content\Category\Service\NavigationLoader;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FooterMainNavSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly NavigationLoader $navigationLoader
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            FooterPageletLoadedEvent::class => 'onFooterLoaded',
        ];
    }

    public function onFooterLoaded(FooterPageletLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        $rootId = $context->getSalesChannel()->getNavigationCategoryId();

        $navigation = $this->navigationLoader->load(
            $rootId, // activeId
            $context,
            $rootId, // rootId
            2        // depth
        );

        $event->getPagelet()->addExtension('mainNavigation', new ArrayEntity([
            'tree' => $navigation->getTree(),
        ]));
    }
}
