<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Akeneo\Component\Localization\Localizer\DateLocalizer;
use Akeneo\Component\Localization\Localizer\NumberLocalizer;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\CollectionNormalizer;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\MetricNormalizer;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\PriceNormalizer;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(
        PriceNormalizer $priceNormalizerFlat,
        MetricNormalizer $metricNormalizerFlat,
        CollectionNormalizer $collectionNormalizer
    ) {
        $this->beConstructedWith($priceNormalizerFlat, $metricNormalizerFlat, $collectionNormalizer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization_of_product_value(ProductValueInterface $value)
    {
        $this->supportsNormalization($value, 'flat')->shouldBe(true);
        $this->supportsNormalization($value, 'csv')->shouldBe(false);
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_value_with_null_data() {
        $standardProductValue = [
            'null_product_value' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => null,
                ],
            ],
        ];
        $this->normalize($standardProductValue, 'flat', [])->shouldReturn(['null_product_value' => '']);
    }

    function it_normalizes_a_metric_product_value(MetricNormalizer $metricNormalizerFlat)
    {
        $standardProductValue = [
            'a_metric' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        'amount' => '12.00',
                        'unit'   => 'KILOGRAM',
                    ],
                ],
            ],
        ];

        $metricNormalizerFlat->normalize($standardProductValue, 'flat', [])->willReturn(
            [
                'a_metric' => '12.00',
                'a_metric-unit' => 'KILOGRAM',
            ]
        );

        $this->normalize($standardProductValue, 'flat', [])->shouldReturn(
            [
                'a_metric' => '12.00',
                'a_metric-unit' => 'KILOGRAM'
            ]
        );
    }

    function it_normalizes_a_price_product_value(PriceNormalizer $priceNormalizerFlat)
    {
        $standardProductValue = [
            'a_price' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => [
                        [
                            'amount'   => '12.00',
                            'currency' => 'EUR',
                        ],
                        [
                            'amount' => '9.50',
                            'currency' => 'USD'
                        ]
                    ],
                ],
            ],
        ];

        $priceNormalizerFlat->normalize($standardProductValue, 'flat', [])->willReturn(
            [
                'a_price-EUR' => '12.00',
                'a_price-USD' => '9.50',
            ]
        );

        $this->normalize($standardProductValue, 'flat', [])->shouldReturn(
            [
                'a_price-EUR' => '12.00',
                'a_price-USD' => '9.50',
            ]
        );
    }

    function it_normalizes_a_collection_product_value(CollectionNormalizer $collectionNormalizer)
    {
        $standardProductValue = [
            'collection' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => ['red', 'blue'],
                ],
            ],
        ];

        $collectionNormalizer->normalize($standardProductValue, 'flat', [])->willReturn(
            [
                'collection' => 'red,blue',
            ]
        );
        $this->normalize($standardProductValue, 'flat', [])->shouldReturn(['collection' => 'red,blue']);
    }

    function it_normalizes_a_scopable_product_value()
    {
        $standardProductValue = [
            'simple_product_value' => [
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'data'   => '12',
                ],
            ],
        ];
        $this->normalize($standardProductValue, 'flat', [])->shouldReturn(['simple_product_value-mobile' => '12']);
    }

    function it_normalizes_a_localizable_product_value()
    {
        $standardProductValue = [
            'simple_product_value' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => '12',
                ],
            ],
        ];
        $this->normalize($standardProductValue, 'flat', [])->shouldReturn(['simple_product_value-fr_FR' => '12']);
    }

    function it_normalizes_a_scopable_and_localizable_product_value()
    {
        $standardProductValue = [
            'simple_product_value' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => 'mobile',
                    'data'   => '12',
                ],
            ],
        ];
        $this->normalize($standardProductValue, 'flat', [])->shouldReturn(['simple_product_value-fr_FR-mobile' => '12']);
    }

}
