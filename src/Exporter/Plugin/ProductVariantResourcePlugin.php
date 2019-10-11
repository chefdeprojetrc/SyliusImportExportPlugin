<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Exporter\Plugin;

use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductVariantResourcePlugin extends ProductResourcePlugin
{
    public function __construct(
        RepositoryInterface $repository,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $entityManager,
        RepositoryInterface $channelPricingRepository,
        RepositoryInterface $productVariantRepository
    ) {
        parent::__construct($repository, $propertyAccessor, $entityManager, $channelPricingRepository, $productVariantRepository);
    }

    protected function getDataForSingleResource(ResourceInterface $resource): void
    {
        if(is_subclass_of($resource, ProductVariantInterface::class)) {
            parent::getDataForSingleResource($resource->getProduct());

            $this->addTranslationData($resource, $this->locale);

            $this->addDataForResource($resource, 'Variant_Code', $resource->getCode());
            $this->addDataForResource($resource, 'Variant_Ean', $resource->getEan());
            $this->addDataForResource($resource, 'Variant_CodeGalitt', $resource->getCodeGalitt());
            $this->addDataForResource($resource, 'Variant_ShippingCategory', $resource->getShippingCategory()->getCode());
            $this->addDataForResource($resource, 'Variant_ShippingRequired', $resource->isShippingRequired());
            $this->addDataForResource($resource, 'Variant_ShippingHeight', $resource->getShippingHeight());
            $this->addDataForResource($resource, 'Variant_ShippingWidth', $resource->getShippingWidth());
            $this->addDataForResource($resource, 'Variant_ShippingDepth', $resource->getShippingDepth());
            $this->addDataForResource($resource, 'Variant_ShippingWeight', $resource->getShippingWeight());

            $this->addPriceData($resource);
        }
    }

    private function addTranslationData(ProductVariantInterface $resource, string $locale): void
    {
        $translation = $resource->getTranslation($locale);

        $this->addDataForResource($resource, 'Variant_Name', $translation->getName());
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

            $this->addDataForResource($resource, 'Variant_Price_'.$channel->getCode(), $channelPricing->getPrice());
        }
    }
}
