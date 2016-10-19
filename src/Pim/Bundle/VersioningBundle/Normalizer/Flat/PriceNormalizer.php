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
     * @param array $standardPrice
     *
     * @return array
     */
    public function normalize($standardPrice, $format = null, array $context = [])
    {
        $flatPrice = [];

        foreach ($standardPrice as $attributeCode => $productValues) {
            foreach ($productValues as $priceValue) {
                $localeCode = $priceValue['locale'];
                $channelCode = $priceValue['scope'];
                foreach ($priceValue['data'] as $price) {
                    $attributeLabel = $this->normalizeAttributeLabel(
                        $attributeCode,
                        $channelCode,
                        $localeCode,
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
        return isset($data['data']) &&
        is_array($data['data']) &&
        isset($data['data'][0]) &&
        isset($data['data'][0]['currency']) &&
        in_array($format, $this->supportedFormats);
    }

    /**
     * Generates the flat label for the collection product value
     *
     * @param string $attributeCode
     * @param string $channelCode
     * @param string $localeCode
     * @param string $currency
     *
     * @return string
     */
    protected function normalizeAttributeLabel($attributeCode, $channelCode, $localeCode, $currency)
    {
        $channelLabel = null !== $channelCode ? self::LABEL_SEPARATOR . $channelCode : '';
        $localeLabel = null !== $localeCode ? self::LABEL_SEPARATOR . $localeCode : '';

        return $attributeCode . self::LABEL_SEPARATOR . $currency . $localeLabel . $channelLabel;
    }
}
