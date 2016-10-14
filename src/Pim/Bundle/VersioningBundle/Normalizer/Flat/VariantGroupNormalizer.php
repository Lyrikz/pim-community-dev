<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a group entity into a flat array
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupNormalizer implements NormalizerInterface
{
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $translationNormalizer;

    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /** @var NormalizerInterface  */
    protected $productValueNormalizer;

    /**
     * @param NormalizerInterface $standardNormalizer
     * @param NormalizerInterface $translationNormalizer
     * @param NormalizerInterface $productValueNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $productValueNormalizer,
        NormalizerInterface $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->productValueNormalizer = $productValueNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param GroupInterface $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $standardGroup = $this->standardNormalizer->normalize($object, 'standard', $context);
        $flatGroup = $standardGroup;
        $flatGroup['axes'] = implode(self::ITEM_SEPARATOR, $standardGroup['axes']);

        unset($flatGroup['values']);
        $flatGroup += $this->normalizeValues($standardGroup['values'], $context);

        unset($flatGroup['labels']);
        $flatGroup += $this->translationNormalizer->normalize($standardGroup['labels'], 'flat', $context);

        return $flatGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return in_array($format, $this->supportedFormats);
    }

    /**
     * Generate an array representing the list of variant group values in flat array
     *
     * @param array $variantGroupValues
     * @param array $context
     *
     * @return array
     */
    protected function normalizeValues($variantGroupValues, $context)
    {
        $flatValues = [];

        foreach ($variantGroupValues as $variantGroupValue) {
            $flatValues += $this->productValueNormalizer->normalize($variantGroupValue, 'flat', $context);
        }

        return $flatValues;
    }
}
