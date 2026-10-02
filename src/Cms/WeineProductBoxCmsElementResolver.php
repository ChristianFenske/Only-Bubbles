<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\Cms\ProductBoxCmsElementResolver;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;

class WeineProductBoxCmsElementResolver extends AbstractCmsElementResolver
{
    public function __construct(
        private readonly ProductBoxCmsElementResolver $inner,
        private readonly SalesChannelRepository $productRepository
    ) {}

    public function getType(): string
    {
        return $this->inner->getType(); // product-box
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $collection = $this->inner->collect($slot, $resolverContext);

        if ($collection === null) {
            return null;
        }

        foreach ($collection as $criteria) {
            if ($criteria instanceof Criteria) {
                $this->addProductAssociations($criteria);
            }
        }

        return $collection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        // 1) Core Resolver -> setzt Data + Product
        $this->inner->enrich($slot, $resolverContext, $result);

        $data = $slot->getData();
        if ($data === null) {
            return;
        }

        // Shopware-Struct
        if (!\method_exists($data, 'getProduct') || !\method_exists($data, 'setProduct')) {
            return;
        }

        $product = $data->getProduct();
        if ($product === null) {
            return;
        }

        // 2) Reload Product inkl. Associations
        $criteria = new Criteria([$product->getId()]);
        $this->addProductAssociations($criteria);

        $reloaded = $this->productRepository
            ->search($criteria, $resolverContext->getSalesChannelContext())
            ->first();

        if ($reloaded === null) {
            return;
        }

        // 3) DEBUG Extension
        $reloaded->addExtension('weine_box_debug', new ArrayStruct([
            'resolverRan'       => true,
            'productId'         => $reloaded->getId(),
            'propertiesNull'    => $reloaded->getProperties() === null,
            'propertiesCount'   => $reloaded->getProperties()?->count() ?? 0,
            'manufacturerNull'  => $reloaded->getManufacturer() === null,
        ]));

        // 4) sortedProperties bauen
        $properties = $reloaded->getProperties();

        if ($properties !== null && $properties->count() > 0) {
            $groups = new PropertyGroupCollection();

            foreach ($properties as $option) {
                $group = $option->getGroup();
                if ($group === null) {
                    continue;
                }

                $existing = $groups->get($group->getId());

                if (!$existing instanceof PropertyGroupEntity) {
                    $group->setOptions(new PropertyGroupOptionCollection());
                    $groups->add($group);
                    $existing = $group;
                }

                $existing->getOptions()?->add($option);
            }

            $reloaded->setSortedProperties($groups);

            $reloaded->addExtension('weine_box_sorted_set', new ArrayStruct([
                'sortedWasSet'      => true,
                'sortedGroupsCount' => $groups->count(),
            ]));
        } else {
            $reloaded->addExtension('weine_box_sorted_set', new ArrayStruct([
                'sortedWasSet' => false,
                'reason'       => 'properties empty',
            ]));
        }

        // 5) Reloaded Product zurücksetzen
        $data->setProduct($reloaded);
        $slot->setData($data);
    }

    private function addProductAssociations(Criteria $criteria): void
    {
        $criteria->addAssociation('manufacturer');

        $criteria->addAssociation('properties');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('properties.translations');
        $criteria->addAssociation('properties.group.translations');

        $criteria->addAssociation('options');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('options.translations');
        $criteria->addAssociation('options.group.translations');
    }
}