<?php declare(strict_types=1);

namespace OnlyBubblesTheme\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Kategorieseiten: Unterkategorien als Pills über den Filtern.
 * - Kategorie mit Unterkategorien (z. B. Entdecken/Rebsorten): "Alle" + Unterkategorien
 * - Unterkategorie ohne eigene Kinder: Geschwister mit aktiver Markierung
 * Template: page.extensions.obSubcategories (component/ob/subcategory-pills.html.twig)
 */
class SubcategoryNavSubscriber implements EventSubscriberInterface
{
    /**
     * @param SalesChannelRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(
        private readonly SalesChannelRepository $categoryRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [NavigationPageLoadedEvent::class => 'onNavigationPage'];
    }

    public function onNavigationPage(NavigationPageLoadedEvent $event): void
    {
        try {
            $page = $event->getPage();
            $category = $page->getCategory();
            if (!$category instanceof CategoryEntity) {
                return;
            }

            $context = $event->getSalesChannelContext();
            $parent = $category;
            $activeId = null;
            $items = $this->loadChildren($category->getId(), $context);

            if ($items->count() === 0) {
                $parentId = $category->getParentId();
                if ($parentId === null) {
                    return;
                }

                $parent = $this->categoryRepository->search(new Criteria([$parentId]), $context)->getEntities()->first();
                // Nur unterhalb von Hauptkategorien (Ebene >= 2), nicht die Hauptnavigation selbst
                if (!$parent instanceof CategoryEntity || $parent->getLevel() < 2) {
                    return;
                }

                $items = $this->loadChildren($parentId, $context);
                $activeId = $category->getId();
            }

            if ($items->count() < 2) {
                return;
            }

            $list = [];
            foreach ($items as $item) {
                $list[] = [
                    'id' => $item->getId(),
                    'name' => (string) ($item->getTranslation('name') ?? $item->getName()),
                    'type' => $item->getType(),
                    'externalLink' => $item->getTranslation('externalLink'),
                ];
            }

            $page->addExtension('obSubcategories', new ArrayStruct([
                'parentId' => $parent->getId(),
                'parentName' => (string) ($parent->getTranslation('name') ?? $parent->getName()),
                'activeId' => $activeId,
                'items' => $list,
            ]));
        } catch (\Throwable $e) {
            $this->logger->warning('OnlyBubblesTheme subcategory pills: ' . $e->getMessage());
        }
    }

    private function loadChildren(string $parentId, SalesChannelContext $context): CategoryCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('parentId', $parentId));
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('visible', true));
        $criteria->setLimit(60);

        /** @var CategoryCollection $children */
        $children = $this->categoryRepository->search($criteria, $context)->getEntities();

        return $children->sortByPosition();
    }
}
