<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Subscriber;

use Shopware\Core\Checkout\Cart\Event\CartLoadedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartPropertiesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $productRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CartLoadedEvent::class => 'onCartLoaded',
        ];
    }

    public function onCartLoaded(CartLoadedEvent $event): void
    {
        $cart = $event->getCart();
        $context = $event->getContext();

        foreach ($cart->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }

            $productId = $lineItem->getReferencedId();
            if (!$productId) {
                continue;
            }

            $criteria = new Criteria([$productId]);
            $criteria->addAssociation('properties.group');

            /** @var ProductEntity|null $product */
            $product = $this->productRepository->search($criteria, $context)->first();

            if (!$product) {
                continue;
            }

            $properties = [];

            foreach ($product->getProperties() as $property) {
                $group = $property->getGroup();

                $groupName = $group ? (string) $group->getName() : '';
                $propertyName = (string) $property->getName();

                $properties[] = [
                    'group' => $groupName,
                    'name' => $propertyName,
                    'sortKey' => $this->getPropertySortKey($groupName),
                    'sortPriority' => $this->getPropertyPriority($groupName),
                ];
            }

            usort($properties, function (array $a, array $b): int {
                $priorityCompare = $a['sortPriority'] <=> $b['sortPriority'];

                if ($priorityCompare !== 0) {
                    return $priorityCompare;
                }

                $groupCompare = strcmp((string) $a['sortKey'], (string) $b['sortKey']);
                if ($groupCompare !== 0) {
                    return $groupCompare;
                }

                return strcmp((string) $a['name'], (string) $b['name']);
            });

            $payload = $lineItem->getPayload();
            $payload['productProperties'] = array_map(static function (array $property): array {
                unset($property['sortKey'], $property['sortPriority']);
                return $property;
            }, $properties);

            $lineItem->setPayload($payload);
        }
    }

    private function getPropertyPriority(string $groupName): int
    {
        $priorities = [
            'jahr' => 10,
            'land' => 20,
            'region' => 30,
            'art' => 40,
            'ausbau' => 50,
            'rebsorten' => 60,
            'alkoholgehalt' => 70,
            'restzucker' => 80,
            'säuregehalt' => 90,
            'allergene' => 100,
            'brennwert' => 110,
            'eiweiß' => 120,
            'gesättigte fettsäuren' => 130,
            'kohlenhydrate' => 140,
            'fett' => 150,
            'zucker' => 160,
            'salz' => 170,
        ];

        $normalized = $this->getPropertySortKey($groupName);

        return $priorities[$normalized] ?? 9999;
    }

    private function getPropertySortKey(string $groupName): string
    {
        $normalized = mb_strtolower(trim($groupName));

        // Leerzeichen vereinheitlichen
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        // Einheiten am Ende entfernen, damit / 100 g und / 100 ml gleich behandelt werden
        $normalized = preg_replace('/\s*\/\s*100\s*(g|ml)\s*$/u', '', $normalized) ?? $normalized;

        return trim($normalized);
    }
}