<?php

namespace Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Normalizer\Standard\GroupNormalizer as StandardNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer to transform a group entity into a flat array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['flat'];

    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    /** @var StandardNormalizer */
    protected $standardNormalizer;

    /**
     * @param StandardNormalizer    $standardNormalizer
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(
        StandardNormalizer $standardNormalizer,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$this->standardNormalizer->supportsNormalization($object, 'standard')) {
            return null;
        }

        $standardGroup = $this->standardNormalizer->normalize($object, 'standard', $context);
        $flatGroup = $standardGroup;

        unset($flatGroup['labels']);
        if ($this->translationNormalizer->supportsNormalization($standardGroup['labels'], 'flat')) {
            $flatGroup += $this->translationNormalizer->normalize($standardGroup['labels'], 'flat', $context);
        }

        return $flatGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof GroupInterface
        && !$data->getType()->isVariant()
        && in_array($format, $this->supportedFormats);
    }
}
