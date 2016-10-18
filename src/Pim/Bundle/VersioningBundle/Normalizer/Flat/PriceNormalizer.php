<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product price
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceNormalizer implements NormalizerInterface
{
    const LABEL_SEPARATOR = '-';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     *
     * @param array $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $flatPrice = [];

        foreach ($object as $attribute => $productValues) {
            foreach ($productValues as $priceValue) {
                $locale = $priceValue['locale'];
                $scope = $priceValue['scope'];
                foreach ($priceValue['data'] as $price) {
                    $attributeLabel = $this->normalizeAttributeLabel(
                        $attribute,
                        $scope,
                        $locale,
                        $price['currency']
                    );
                    $flatPrice[$attributeLabel] = $price['amount'];
                }
            }
        }

        return $flatPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return in_array($format, $this->supportedFormats);
    }

    /**
     * Generates the flat label for the collection product value
     *
     * @param string $attribute
     * @param string $scope
     * @param string $locale
     * @param string $currency
     *
     * @return string
     */
    protected function normalizeAttributeLabel($attribute, $scope, $locale, $currency)
    {
        $scopeLabel = null !== $scope ? self::LABEL_SEPARATOR . $scope : '';
        $localeLabel = null !== $locale ? self::LABEL_SEPARATOR . $locale : '';

        return $attribute . self::LABEL_SEPARATOR . $currency . $scopeLabel . $localeLabel;
    }
}
