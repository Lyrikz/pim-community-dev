<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Normalizer\Standard\AttributeOptionNormalizer as StandardNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize an attribute option
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       Pim\Bundle\TransformBundle\Normalizer\Flat\ProductNormalizer
 */
class AttributeOptionNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var StandardNormalizer */
    protected $standardNormalizer;

    /** @var TranslationNormalizer  */
    protected $translationNormalizer;

    /**
     * AttributeNormalizer constructor.
     *
     * @param NormalizerInterface   $standardNormalizer
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(
        NormalizerInterface $standardNormalizer,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeOptionInterface $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $standardAttributeOption = $this->standardNormalizer->normalize($object, 'standard', $context);
        $flatAttributeOption = $standardAttributeOption;

        unset($flatAttributeOption['labels']);
        $flatAttributeOption += $this->translationNormalizer->normalize(
            $standardAttributeOption['labels'],
            'flat',
            $context
        );

        return $flatAttributeOption;
    }


    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeOptionInterface && in_array($format, $this->supportedFormats);
    }
}
