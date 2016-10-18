<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a metric data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricNormalizer implements NormalizerInterface
{
    const LABEL_SEPARATOR = '-';
    const VALUE_SEPARATOR = ' ';
    const MULTIPLE_FIELDS_FORMAT = 'multiple_fields';
    const SINGLE_FIELD_FORMAT = 'single_field';
    const UNIT_LABEL = 'unit';

    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /**
     * {@inheritdoc}
     *
     * @param array $standardMetric
     *
     * @return array
     */
    public function normalize($standardMetric, $format = null, array $context = [])
    {
        $context = $this->resolveContext($context);

        $flatMetric = [];

        foreach ($standardMetric as $attributeCode => $productValues) {
            foreach ($productValues as $metricValue) {
                $localeCode = $metricValue['locale'];
                $channelCode = $metricValue['scope'];

                $attributeLabel = $this->normalizeAttributeLabel(
                    $attributeCode,
                    $channelCode,
                    $localeCode,
                    $isUnitLabel = false
                );

                if (self::MULTIPLE_FIELDS_FORMAT === $context['metric_format']) {
                    $attributeUnitLabel = $this->normalizeAttributeLabel(
                        $attributeCode,
                        $channelCode,
                        $localeCode,
                        $isUnitLabel = true
                    );

                    $flatMetric[$attributeLabel] = $metricValue['data']['amount'];
                    $flatMetric[$attributeUnitLabel] = $metricValue['data']['unit'];
                } elseif (self::SINGLE_FIELD_FORMAT === $context['metric_format']) {
                    $flatMetric[$attributeLabel] = '';

                    if ('' !== $metricValue['data']['amount'] && '' !== $metricValue['data']['unit']) {
                        $flatMetric[$attributeLabel] = $metricValue['data']['amount'] .
                            self::VALUE_SEPARATOR .
                            $metricValue['data']['unit'];
                    }
                } else {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Value "%s" of "metric_format" context value is not allowed ' .
                            '(allowed values: "single_field, multiple_fields"',
                            $context['metric_format']
                        )
                    );
                }
            }
        }

        return $flatMetric;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return in_array($format, $this->supportedFormats);
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    protected function resolveContext(array $context = [])
    {
        $context = array_merge(['metric_format' => self::MULTIPLE_FIELDS_FORMAT], $context);

        if (!in_array($context['metric_format'], [self::MULTIPLE_FIELDS_FORMAT, self::SINGLE_FIELD_FORMAT])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Value "%s" of "metric_format" context value is not allowed ' .
                    '(allowed values: "single_field, multiple_fields"',
                    $context['metric_format']
                )
            );
        }

        return $context;
    }

    /**
     * Generates the attribute label based on unit, scope, locale.
     *
     * Generates the unit attribute label or the attribute label based on the $isUnitLabel parameter
     *
     * @param string $attributeCode
     * @param string $channelCode
     * @param string $localeCode
     * @param boolean $isUnitLabel
     *
     * @return string
     */
    protected function normalizeAttributeLabel($attributeCode, $channelCode, $localeCode, $isUnitLabel)
    {
        $channelLabel = null !== $channelCode ? self::LABEL_SEPARATOR . $channelCode : '';
        $localeLabel = null !== $localeCode ? self::LABEL_SEPARATOR . $localeCode : '';
        $unitLabel = true === $isUnitLabel ?
            self::LABEL_SEPARATOR . self::UNIT_LABEL : '';

        return $attributeCode . $unitLabel . $localeLabel . $channelLabel;
    }
}
