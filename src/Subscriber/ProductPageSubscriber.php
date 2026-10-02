<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $categoryRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $page = $event->getPage();
        $product = $page->getProduct();

        if (!$product) {
            return;
        }

        $context = $event->getContext();
        $rebsorten = [];

        foreach ($product->getProperties() as $property) {
            $group = $property->getGroup();
            $groupName = $group?->getTranslation('name') ?? $group?->getName();

            if ($groupName !== 'Rebsorten') {
                continue;
            }

            $name = $property->getTranslation('name') ?? $property->getName();

            if ($name) {
                $rebsorten[] = trim($name);
            }
        }

        $rebsorten = array_values(array_unique(array_filter($rebsorten)));

        /*
         * Haupt-Kategorie "Rebsorten" laden
         */
        $mainCategory = null;

        $mainCriteria = new Criteria();
        $mainCriteria->setLimit(1);
        $mainCriteria->addFilter(new EqualsFilter('active', true));
        $mainCriteria->addFilter(new EqualsFilter('translations.name', 'Rebsorten'));
        $mainCriteria->addAssociation('media');
        $mainCriteria->addAssociation('children');
        $mainCriteria->addAssociation('children.media');

        /** @var CategoryEntity|null $mainCategoryEntity */
        $mainCategoryEntity = $this->categoryRepository
            ->search($mainCriteria, $context)
            ->first();

        if ($mainCategoryEntity) {
            $mainChildren = [];

            foreach ($mainCategoryEntity->getChildren() ?? [] as $child) {
                if (!$child->getActive()) {
                    continue;
                }

                $mainChildren[] = [
                    'id' => $child->getId(),
                    'name' => $child->getTranslation('name') ?? $child->getName(),
                    'description' => $child->getTranslation('description') ?? $child->getDescription(),
                    'mediaUrl' => $child->getMedia()?->getUrl(),
                    'mediaAlt' => $child->getMedia()?->getTranslated()['alt'] ?? ($child->getTranslation('name') ?? $child->getName()),
                ];
            }

            $mainCategory = [
                'id' => $mainCategoryEntity->getId(),
                'name' => $mainCategoryEntity->getTranslation('name') ?? $mainCategoryEntity->getName(),
                'description' => $mainCategoryEntity->getTranslation('description') ?? $mainCategoryEntity->getDescription(),
                'mediaUrl' => $mainCategoryEntity->getMedia()?->getUrl(),
                'mediaAlt' => $mainCategoryEntity->getMedia()?->getTranslated()['alt'] ?? ($mainCategoryEntity->getTranslation('name') ?? $mainCategoryEntity->getName()),
                'children' => $mainChildren,
            ];
        }

        /*
         * Rebsorten-Einzelkategorien laden
         */
        $mapped = [];

        if (!empty($rebsorten)) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('active', true));
            $criteria->addFilter(new EqualsAnyFilter('translations.name', $rebsorten));
            $criteria->addAssociation('media');
            $criteria->addAssociation('children');
            $criteria->addAssociation('children.media');

            $categories = $this->categoryRepository
                ->search($criteria, $context)
                ->getEntities();

            /** @var CategoryEntity $category */
            foreach ($categories as $category) {
                $children = [];

                foreach ($category->getChildren() ?? [] as $child) {
                    if (!$child->getActive()) {
                        continue;
                    }

                    $children[] = [
                        'id' => $child->getId(),
                        'name' => $child->getTranslation('name') ?? $child->getName(),
                        'description' => $child->getTranslation('description') ?? $child->getDescription(),
                        'mediaUrl' => $child->getMedia()?->getUrl(),
                        'mediaAlt' => $child->getMedia()?->getTranslated()['alt'] ?? ($child->getTranslation('name') ?? $child->getName()),
                    ];
                }

                $mapped[] = [
                    'id' => $category->getId(),
                    'name' => $category->getTranslation('name') ?? $category->getName(),
                    'description' => $category->getTranslation('description') ?? $category->getDescription(),
                    'mediaUrl' => $category->getMedia()?->getUrl(),
                    'mediaAlt' => $category->getMedia()?->getTranslated()['alt'] ?? ($category->getTranslation('name') ?? $category->getName()),
                    'children' => $children,
                ];
            }
        }

        $page->addExtension('rebsorteCategories', new ArrayStruct([
            'mainCategory' => $mainCategory,
            'items' => $mapped,
        ]));
    }
}