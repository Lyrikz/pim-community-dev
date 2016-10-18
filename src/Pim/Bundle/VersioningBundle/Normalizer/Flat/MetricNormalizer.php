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
     * @param array $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context = $this->resolveContext($context);

        $flatMetric = [];

        foreach ($object as $attribute => $productValues) {
            foreach ($productValues as $metricValue) {
                $locale = $metricValue['locale'];
                $scope = $metricValue['scope'];

                $attributeLabel = $this->normalizeAttributeLabel(
                    $attribute,
                    $scope,
                    $locale
                );

                if (self::MULTIPLE_FIELDS_FORMAT === $context['metric_format']) {
                    $attributeUnit = $this->normalizeAttributeLabel(
                        $attribute,
                        $scope,
                        $locale,
                        $isUnit = true
                    );

                    $flatMetric[$attributeLabel] = $metricValue['data']['amount'];
                    $flatMetric[$attributeUnit] = $metricValue['data']['unit'];
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
     * Generates the attribute label based on unit, scope, locale and context
     *
     * @param string $attribute
     * @param string $scope
     * @param string $locale
     * @param bool   $isUnit
     *
     * @return string
     */
    protected function normalizeAttributeLabel($attribute, $scope, $locale, $isUnit = false)
    {
        $scopeLabel = null !== $scope ? self::LABEL_SEPARATOR . $scope : '';
        $localeLabel = null !== $locale ? self::LABEL_SEPARATOR . $locale : '';
        $unitLabel = false !== $isUnit ? self::LABEL_SEPARATOR . self::UNIT_LABEL : '';

        return $attribute . $unitLabel . $localeLabel . $scopeLabel;
    }
}
