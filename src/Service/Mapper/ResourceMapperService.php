<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Attribute\Model\AttributeSubjectInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class ResourceMapperService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function map(ResourceInterface $resource): array {
        $fields = $this->entityManager->getClassMetadata(\get_class($resource))->getFieldNames();

        if(is_subclass_of($resource, AttributeSubjectInterface::class)) {
//            dump($resource->getAttributesByLocale('fr_FR', 'fr_FR'));
        }

        return [];
    }
}
