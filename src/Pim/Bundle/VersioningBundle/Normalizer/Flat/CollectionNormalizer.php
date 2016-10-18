<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value collection
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer
 */
class CollectionNormalizer implements NormalizerInterface
{
    const LABEL_SEPARATOR = '-';
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     *
     * @param array $collection
     *
     * @return array
     */
    public function normalize($collection, $format = null, array $context = [])
    {
        $flatCollection = [];

        foreach ($collection as $attributeCode => $productValues) {
            foreach ($productValues as $collectionValue) {
                $attributeLabel = $this->normalizeAttributeLabel(
                    $attributeCode,
                    $collectionValue['scope'],
                    $collectionValue['locale']
                );

                $flatCollection[$attributeLabel] = implode(self::ITEM_SEPARATOR, $collectionValue['data']);
            }
        }

        return $flatCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return isset($data['data']) && is_array($data['data']) && in_array($format, $this->supportedFormats);
    }

    /**
     * Generates the flat label for the price product value
     *
     * @param string $attributeCode
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return string
     */
    protected function normalizeAttributeLabel($attributeCode, $channelCode, $localeCode)
    {
        $channelLabel = null !== $channelCode ? self::LABEL_SEPARATOR . $channelCode : '';
        $localeLabel = null !== $localeCode ? self::LABEL_SEPARATOR . $localeCode : '';

        return $attributeCode . $localeLabel . $channelLabel;
    }
}
