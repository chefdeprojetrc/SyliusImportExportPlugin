<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductVariantResourcePlugin extends ResourcePlugin
{
    /** @var RepositoryInterface */
    private $channelPricingRepository;
    /** @var RepositoryInterface */
    private $productVariantRepository;

    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        RepositoryInterface $channelPricingRepository,
        RepositoryInterface $productVariantRepository
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager);
        $this->channelPricingRepository = $channelPricingRepository;
        $this->productVariantRepository = $productVariantRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $idsToExport, string $locale): void
    {
        $this->resources = $this->findResources($idsToExport);
        $this->locale = $locale;

        /** @var ProductVariantInterface $resource */
        foreach ($this->resources as $resource) {
            $this->addTranslationData($resource, $locale);

            $this->addDataForResource($resource, 'Product_code', $resource->getProduct()->getCode());
            $this->addDataForResource($resource, 'Code', $resource->getCode());
            $this->addDataForResource($resource, 'Ean', $resource->getEan());
            $this->addDataForResource($resource, 'CodeGalitt', $resource->getCodeGalitt());
            $this->addDataForResource($resource, 'ShippingCategory', $resource->getShippingCategory()->getCode());
            $this->addDataForResource($resource, 'ShippingRequired', $resource->isShippingRequired());
            $this->addDataForResource($resource, 'ShippingHeight', $resource->getShippingHeight());
            $this->addDataForResource($resource, 'ShippingWidth', $resource->getShippingWidth());
            $this->addDataForResource($resource, 'ShippingDepth', $resource->getShippingDepth());
            $this->addDataForResource($resource, 'ShippingWeight', $resource->getShippingWeight());

            $this->addPriceData($resource);
        }
    }

    private function addTranslationData(ProductVariantInterface $resource, string $locale): void
    {
        $translation = $resource->getTranslation($locale);

        $this->addDataForResource($resource, 'Locale', $translation->getLocale());
        $this->addDataForResource($resource, 'Name', $translation->getName());
    }

    private function addPriceData(ProductVariantInterface $resource): void
    {
        /** @var \Sylius\Component\Core\Model\ChannelInterface[] $channel */
        $channels = $resource->getProduct()->getChannels();
        foreach ($channels as $channel) {
            $channelPricing = $this->channelPricingRepository->findOneBy([
                'channelCode' => $channel->getCode(),
                'productVariant' => $resource,
            ]);

            $this->addDataForResource($resource, 'Price_'.$channel->getCode(), $channelPricing->getPrice());
        }
    }
}
