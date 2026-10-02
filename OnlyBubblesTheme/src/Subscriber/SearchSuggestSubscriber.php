<?php declare(strict_types=1);

namespace OnlyBubblesTheme\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Such-Vorschläge (Header-Suche): Kategorien der Treffer mit Anzahl für das Only-Bubbles-Dropdown.
 * Ergebnis im Template: page.searchResult.extensions.obSuggest.categories
 * Fehler hier dürfen die Suche nie stören – daher alles in try/catch.
 */
class SearchSuggestSubscriber implements EventSubscriberInterface
{
    private const AGGREGATION = 'ob-suggest-categories';
    private const LIMIT = 3;

    /**
     * @param EntityRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(
        private readonly EntityRepository $categoryRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_SUGGEST_CRITERIA => 'onCriteria',
            ProductEvents::PRODUCT_SUGGEST_RESULT => 'onResult',
        ];
    }

    public function onCriteria(ProductSuggestCriteriaEvent $event): void
    {
        try {
            $event->getCriteria()->addAggregation(
                new TermsAggregation(self::AGGREGATION, 'product.categoriesRo.id', 60)
            );
        } catch (\Throwable $e) {
            $this->logger->warning('OnlyBubblesTheme search suggest: ' . $e->getMessage());
        }
    }

    public function onResult(ProductSuggestResultEvent $event): void
    {
        try {
            $result = $event->getResult();
            $aggregation = $result->getAggregations()->get(self::AGGREGATION);
            if (!$aggregation instanceof TermsResult) {
                return;
            }

            $counts = [];
            foreach ($aggregation->getBuckets() as $bucket) {
                $counts[(string) $bucket->getKey()] = $bucket->getCount();
            }
            // Nicht im Dropdown anzeigen – sonst landet das in der Filter-Logik
            $result->getAggregations()->remove(self::AGGREGATION);

            if ($counts === []) {
                return;
            }

            $criteria = new Criteria(array_keys($counts));
            $categories = $this->categoryRepository->search($criteria, $event->getContext())->getEntities();

            $candidates = [];
            /** @var CategoryEntity $category */
            foreach ($categories as $category) {
                if ($category->getParentId() === null
                    || !$category->getActive()
                    || !$category->getVisible()
                    || $category->getType() !== 'page') {
                    continue;
                }

                $candidates[$category->getId()] = [
                    'id' => $category->getId(),
                    'name' => (string) ($category->getTranslation('name') ?? $category->getName()),
                    'count' => $counts[$category->getId()] ?? 0,
                    'path' => (string) $category->getPath(),
                ];
            }

            // Oberkategorie weglassen, wenn eine Unterkategorie dieselben Treffer hat
            foreach ($candidates as $id => $candidate) {
                foreach ($candidates as $other) {
                    if ($other['id'] !== $id
                        && str_contains($other['path'], '|' . $id . '|')
                        && $other['count'] === $candidate['count']) {
                        unset($candidates[$id]);
                        break;
                    }
                }
            }

            usort($candidates, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);

            $list = array_map(
                static fn (array $c): array => ['id' => $c['id'], 'name' => $c['name'], 'count' => $c['count']],
                \array_slice($candidates, 0, self::LIMIT)
            );

            $result->addExtension('obSuggest', new ArrayStruct(['categories' => $list]));
        } catch (\Throwable $e) {
            $this->logger->warning('OnlyBubblesTheme search suggest: ' . $e->getMessage());
        }
    }
}
