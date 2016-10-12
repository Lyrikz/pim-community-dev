<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Pim\Component\Catalog\Normalizer\Standard\ProductNormalizer as StandardNormalizer;

/**
 * A normalizer to transform a product entity into a flat array
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /** @staticvar string */
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var StandardNormalizer */
    protected $standardNormalizer;

    /**
     * @param StandardNormalizer $standardNormalizer
     */
    public function __construct(StandardNormalizer $standardNormalizer)
    {
        $this->standardNormalizer = $standardNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->standardNormalizer->supportsNormalization($object, 'standard')) {
            return null;
        }

        $standardProduct = $this->standardNormalizer->normalize($object, 'standard', $context);
        $flatProduct = $standardProduct;

        $flatProduct['groups'] = implode(self::ITEM_SEPARATOR, $standardProduct['groups']);
        $flatProduct['categories'] = implode(self::ITEM_SEPARATOR, $standardProduct['categories']);
        $flatProduct['associations'] = $this->normalizeAssociations($standardProduct['associations']);
        $flatProduct['values'] = $this->normalizeValues($standardProduct['values']);

        unset($flatProduct['createdAt']);
        unset($flatProduct['updatedAt']);


        return $flatProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize associations
     *
     * @param array $associations
     *
     * @return array
     */
    protected function normalizeAssociations($associations = [])
    {
        $flatAssociations = [];

        foreach ($associations as $associationType => $association) {
            $flatAssociations[$associationType.'-groups'] = implode(',', $association['groups']);
            $flatAssociations[$associationType.'-products'] = implode(',', $association['products']);
        }

        return $flatAssociations;
    }
}
