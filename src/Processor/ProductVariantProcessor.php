<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Processor;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Importer\Transformer\TransformerPoolInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Repository\ProductImageRepositoryInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\AttributeCodesProviderInterface;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProvider;
use FriendsOfSylius\SyliusImportExportPlugin\Service\ImageTypesProviderInterface;
use Ramsey\Uuid\Uuid;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductTaxonRepository;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Generator\SlugGeneratorInterface;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProductVariantProcessor implements ResourceProcessorInterface
{
    /** @var RepositoryInterface */
    private $channelPricingRepository;
    /** @var FactoryInterface */
    private $channelPricingFactory;
    /** @var ChannelRepositoryInterface */
    private $channelRepository;
    /** @var FactoryInterface */
    private $productTaxonFactory;
    /** @var ProductTaxonRepository */
    private $productTaxonRepository;
    /** @var FactoryInterface */
    private $productImageFactory;
    /** @var ProductImageRepositoryInterface */
    private $productImageRepository;
    /** @var ImageTypesProviderInterface */
    private $imageTypesProvider;
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $manager;
    /** @var TransformerPoolInterface|null */
    private $transformerPool;
    /** @var ProductFactoryInterface */
    private $resourceProductFactory;
    /** @var TaxonFactoryInterface */
    private $resourceTaxonFactory;
    /** @var ProductVariantRepositoryInterface */
    private $productRepository;
    /** @var TaxonRepositoryInterface */
    private $taxonRepository;
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;
    /** @var MetadataValidatorInterface */
    private $metadataValidator;
    /** @var array */
    private $headerKeys;
    /** @var array */
    private $attrCode;
    /** @var array */
    private $imageCode;
    /** @var RepositoryInterface */
    private $productAttributeRepository;
    /** @var FactoryInterface */
    private $productAttributeValueFactory;
    /** @var AttributeCodesProviderInterface */
    private $attributeCodesProvider;
    /** @var SlugGeneratorInterface */
    private $slugGenerator;
    /** @var FactoryInterface */
    private $productVariantFactory;
    /** @var RepositoryInterface */
    private $productVariantRepository;
    /** @var FactoryInterface */
    private $productTranslationFactory;
    /** @var Slugify  */
    private $slugify;

    public function __construct(
        ProductFactoryInterface $productFactory,
        TaxonFactoryInterface $taxonFactory,
        RepositoryInterface $productRepository,
        TaxonRepositoryInterface $taxonRepository,
        MetadataValidatorInterface $metadataValidator,
        PropertyAccessorInterface $propertyAccessor,
        RepositoryInterface $productAttributeRepository,
        AttributeCodesProviderInterface $attributeCodesProvider,
        FactoryInterface $productAttributeValueFactory,
        ChannelRepositoryInterface $channelRepository,
        FactoryInterface $productTaxonFactory,
        FactoryInterface $productImageFactory,
        FactoryInterface $productVariantFactory,
        FactoryInterface $channelPricingFactory,
        ProductTaxonRepository $productTaxonRepository,
        ProductImageRepositoryInterface $productImageRepository,
        RepositoryInterface $productVariantRepository,
        RepositoryInterface $channelPricingRepository,
        ImageTypesProviderInterface $imageTypesProvider,
        SlugGeneratorInterface $slugGenerator,
        ?TransformerPoolInterface $transformerPool,
        EntityManagerInterface $manager,
        FactoryInterface $syliusFactoryProductTranslation,
        RepositoryInterface $syliusShippingCategory,
        array $headerKeys
    ) {
        $this->resourceProductFactory = $productFactory;
        $this->resourceTaxonFactory = $taxonFactory;
        $this->productRepository = $productRepository;
        $this->taxonRepository = $taxonRepository;
        $this->metadataValidator = $metadataValidator;
        $this->propertyAccessor = $propertyAccessor;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeValueFactory = $productAttributeValueFactory;
        $this->attributeCodesProvider = $attributeCodesProvider;
        $this->headerKeys = $headerKeys;
        $this->slugGenerator = $slugGenerator;
        $this->transformerPool = $transformerPool;
        $this->manager = $manager;
        $this->channelRepository = $channelRepository;
        $this->productTaxonFactory = $productTaxonFactory;
        $this->productTaxonRepository = $productTaxonRepository;
        $this->productImageFactory = $productImageFactory;
        $this->productImageRepository = $productImageRepository;
        $this->imageTypesProvider = $imageTypesProvider;
        $this->productVariantFactory = $productVariantFactory;
        $this->productVariantRepository = $productVariantRepository;
        $this->channelPricingFactory = $channelPricingFactory;
        $this->channelPricingRepository = $channelPricingRepository;
        $this->productTranslationFactory = $syliusFactoryProductTranslation;
        $this->syliusShippingCategory = $syliusShippingCategory;
        $this->slugify = new Slugify();
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data): void
    {
        $this->attrCode = $this->attributeCodesProvider->getAttributeCodesList();
        $this->imageCode = $this->imageTypesProvider->getProductImagesCodesWithPrefixList();

        $this->headerKeys = \array_merge($this->headerKeys, $this->attrCode);
        $this->headerKeys = \array_merge($this->headerKeys, $this->imageCode);
        //$this->metadataValidator->validateHeaders($this->headerKeys, $data);

        $product = $this->getProduct($data);

        $variant = $this->getProductVariant($data['Code']);
        $variant->setProduct($product);
        $variant->setCurrentLocale($data['Locale']);
        $variant->setCurrentLocale($data['Locale']);
        $variant->setName(substr($data['Name'], 0, 255));
        $variant->setCode($data['Code'] ?: (string) Uuid::uuid4());

        $variant->setEan($data['Ean']);
        $variant->setCodeGalitt($data['CodeGalitt']);
        $variant->setShippingRequired(empty($data['ShippingRequired']));
        $variant->setWidth($data['ShippingWidth']);
        $variant->setHeight($data['ShippingHeight']);
        $variant->setDepth($data['ShippingDepth']);
        $variant->setWeight($data['ShippingWeight']);

        $shippingCategory = $this->syliusShippingCategory->findOneBy(['code' => $data['ShippingCategory']]);
        $variant->setShippingCategory($shippingCategory);

        foreach ($product->getChannels() as $channel) {
            $channelCode = $channel->getCode();
            $channelPricing = $this->channelPricingRepository->findOneBy([
                'channelCode' => $channelCode,
                'productVariant' => $variant,
            ]);

            if (null === $channelPricing) {
                /** @var ChannelPricingInterface $channelPricing */
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channelCode);
                $variant->addChannelPricing($channelPricing);
            }

            $channelPricing->setPrice((int) $data['Price_'.$channelCode]);
            $channelPricing->setOriginalPrice((int) $data['Price_'.$channelCode]);
        }

        $this->productVariantRepository->add($variant);
    }

    private function getProduct(array $data): ProductInterface
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $data['Product_code']]);
        if (null === $product) {
            throw new \Exception($data['Product_code']. ' not found');
        }

        return $product;
    }

    private function getProductVariant(string $code): ProductVariantInterface
    {
        /** @var ProductVariantInterface|null $productVariant */
        $productVariant = $this->productVariantRepository->findOneBy(['code' => $code]);
        if ($productVariant === null) {
            /** @var ProductVariantInterface $productVariant */
            $productVariant = $this->productVariantFactory->createNew();
            $productVariant->setCode($code);
        }

        return $productVariant;
    }

    private function setMainTaxon(ProductInterface $product, array $data): void
    {
        /** @var Taxon|null $taxon */
        $taxon = $this->taxonRepository->findOneBy(['code' => $data['Main_taxon']]);
        if ($taxon === null) {
            return;
        }

        /** @var ProductInterface $product */
        $product->setMainTaxon($taxon);

        $this->addTaxonToProduct($product, $data['Main_taxon']);
    }

    private function setTaxons(ProductInterface $product, array $data): void
    {
        $taxonCodes = \explode('|', $data['Taxons']);
        foreach ($taxonCodes as $taxonCode) {
            if ($taxonCode !== $data['Main_taxon']) {
                $this->addTaxonToProduct($product, $taxonCode);
            }
        }
    }

    private function setAttributesData(ProductInterface $product, array $data): void
    {
        foreach ($this->attrCode as $attrCode) {
            $attributeValue = $product->getAttributeByCodeAndLocale($attrCode);

            if (empty($data[$attrCode])) {
                if ($attributeValue !== null) {
                    $product->removeAttribute($attributeValue);
                }

                continue;
            }

            if ($attributeValue !== null) {
                if (null !== $this->transformerPool) {
                    $data[$attrCode] = $this->transformerPool->handle(
                        $attributeValue->getType(),
                        $data[$attrCode]
                    );
                }

                $attributeValue->setValue($data[$attrCode]);

                continue;
            }

            $this->setAttributeValue($product, $data, $attrCode);
        }
    }

    private function setDetails(ProductInterface $product, array $data): void
    {
        $product->setCurrentLocale($data['Locale']);
        $product->setFallbackLocale($data['Locale']);

        $translation = $product->getTranslation($data['Locale']);
        $translation->setName($data['Name']);
        $translation->setDescription($data['Description']);
        $translation->setShortDescription($data['Short_description']);
        $translation->setMetaDescription($data['Meta_description']);
        $translation->setMetaKeywords($data['Meta_keywords']);
        $translation->setSlug($data['link_rewrite']);
    }

    private function setVariant(ProductInterface $product, array $data): void
    {
        $productVariant = $this->getProductVariant($product->getCode());
        $productVariant->setCurrentLocale($data['Locale']);
        $productVariant->setCurrentLocale($data['Locale']);
        $productVariant->setName(substr($data['Name'], 0, 255));

        $channels = \explode('|', $data['Channels']);
        foreach ($channels as $channelCode) {
            $channelPricing = $this->channelPricingRepository->findOneBy([
                'channelCode' => $channelCode,
                'productVariant' => $productVariant,
            ]);

            if (null === $channelPricing) {
                /** @var ChannelPricingInterface $channelPricing */
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channelCode);
                $productVariant->addChannelPricing($channelPricing);
            }

            $channelPricing->setPrice((int) $data['Price']);
            $channelPricing->setOriginalPrice((int) $data['Price']);
        }

        $product->addVariant($productVariant);
    }
}
