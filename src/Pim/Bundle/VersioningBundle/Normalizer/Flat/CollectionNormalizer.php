<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpCollection\CollectionInterface;
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
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $flatCollection = [];

        foreach ($object as $attribute => $productValues) {
            foreach ($productValues as $collectionValue) {
                $attributeLabel = $this->normalizeAttributeLabel(
                    $attribute,
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
     *
     * @param CollectionInterface $channel
     *
     * @return array
     */
    public function supportsNormalization($data, $format = null)
    {
        return in_array($format, $this->supportedFormats);
    }

    /**
     * Generates the flat label for the price product value
     *
     * @param string $attribute
     * @param string $scope
     * @param string $locale
     *
     * @return string
     */
    protected function normalizeAttributeLabel($attribute, $scope, $locale)
    {
        $scopeLabel = null !== $scope ? self::LABEL_SEPARATOR . $scope : '';
        $localeLabel = null !== $locale ? self::LABEL_SEPARATOR . $locale : '';

        return $attribute . $localeLabel . $scopeLabel;
    }
}
