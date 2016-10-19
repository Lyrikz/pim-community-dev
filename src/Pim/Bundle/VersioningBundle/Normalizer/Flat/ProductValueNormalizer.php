<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a product value into an array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    const LABEL_SEPARATOR = '-';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var NormalizerInterface */
    protected $priceNormalizer;

    /** @var NormalizerInterface */
    protected $metricNormalizer;

    /** @var NormalizerInterface */
    protected $collectionNormalizer;

    /**
     * @param NormalizerInterface $priceNormalizer
     * @param NormalizerInterface $metricNormalizer
     * @param NormalizerInterface $collectionNormalizer
     */
    public function __construct(
        NormalizerInterface $priceNormalizer,
        NormalizerInterface $metricNormalizer,
        NormalizerInterface $collectionNormalizer
    ) {
        $this->priceNormalizer = $priceNormalizer;
        $this->metricNormalizer = $metricNormalizer;
        $this->collectionNormalizer = $collectionNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * This method may cause problems in the future as it guesses which normalizer to call based on
     * the product value structure.
     * Waiting for the 2.0 'type' product value key to properly use the right serializer.
     *
     * @param array $standardProductValue
     *
     * @return array
     */
    public function normalize($standardProductValue, $format = null, array $context = [])
    {
        $flatProductValue = [];

        foreach ($standardProductValue as $attributeCode => $productValues) {
            foreach ($productValues as $productValue) {
                if (isset($productValue['data']['unit'])) {
                    $flatProductValue += (array) $this->metricNormalizer->normalize(
                        $standardProductValue,
                        'flat',
                        $context
                    );
                } elseif (is_array($productValue['data'][0]) && isset($productValue['data'][0]['currency'])) {
                    $flatProductValue += (array) $this->priceNormalizer->normalize(
                        $standardProductValue,
                        'flat',
                        $context
                    );
                } elseif (is_array($productValue['data'])) {
                    $flatProductValue += $this->collectionNormalizer->normalize(
                        $standardProductValue,
                        'flat',
                        $context
                    );
                } else {
                    $attributeLabel = $this->normalizeAttributeLabel(
                        $attributeCode,
                        $productValue['scope'],
                        $productValue['locale']
                    );

                    $flatProductValue[$attributeLabel] = null !== $productValue['data'] ? $productValue['data'] : '';
                }
            }
        }

        return $flatProductValue;
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
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return string
     */
    protected function normalizeAttributeLabel($attribute, $channelCode, $localeCode)
    {
        $channelLabel = null !== $channelCode ? self::LABEL_SEPARATOR . $channelCode : '';
        $localeLabel = null !== $localeCode ? self::LABEL_SEPARATOR . $localeCode : '';

        return $attribute . $localeLabel . $channelLabel;
    }
}
