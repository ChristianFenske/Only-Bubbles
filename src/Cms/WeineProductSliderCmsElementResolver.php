<?php declare(strict_types=1);

namespace WeineFeinkostTheme\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;
use Shopware\Core\Content\Product\Cms\ProductSliderStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Content\Product\ProductCollection;

class WeineProductSliderCmsElementResolver extends AbstractCmsElementResolver
{
    public function __construct(
        private readonly ProductSliderCmsElementResolver $inner,
        private readonly SalesChannelRepository $productRepository
    ) {
    }

    public function getType(): string
    {
        return $this->inner->getType();
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $collection = $this->inner->collect($slot, $resolverContext);

        if ($collection === null) {
            return null;
        }

        // Force associations on ALL criteria created by the product-slider resolver
        foreach ($collection as $criteria) {
            if ($criteria instanceof Criteria) {
                $criteria->addAssociation('manufacturer');
                $criteria->addAssociation('properties.group');
            }
        }

        return $collection;
    }

	public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
	{
		$this->inner->enrich($slot, $resolverContext, $result);

		$data = $slot->getData();
		if (!$data || !method_exists($data, 'getProducts') || !method_exists($data, 'setProducts')) {
			return;
		}

		$products = $data->getProducts();
		if (!$products || $products->count() === 0) {
			return;
		}

		$ids = $products->getIds();

		$criteria = new Criteria($ids);
		$criteria->addAssociation('manufacturer');
		$criteria->addAssociation('properties.group');
		$criteria->addAssociation('options.group');

		// (optional) if you want only products in $ids (Criteria($ids) already does that)
		// $criteria->addFilter(new EqualsAnyFilter('product.id', $ids));

		$salesChannelContext = $resolverContext->getSalesChannelContext();
		$reloaded = $this->productRepository->search($criteria, $salesChannelContext)->getEntities();

		// reorder to match slider order
		$sorted = new ProductCollection();

		foreach ($ids as $id) {
			$p = $reloaded->get($id);
			if ($p instanceof ProductEntity) {
				$sorted->add($p);
			}
		}

		$data->setProducts($sorted);
		$slot->setData($data);
	}
}