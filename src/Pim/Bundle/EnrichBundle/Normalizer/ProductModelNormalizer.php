<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Pim\Bundle\EnrichBundle\Provider\Form\FormProviderInterface;
use Pim\Component\Catalog\Association\MissingAssociationAdder;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\ProductModel\ImageAsLabel;
use Pim\Component\Catalog\ProductModel\Query\VariantProductRatioInterface;
use Pim\Component\Catalog\ValuesFiller\EntityWithFamilyValuesFillerInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Pim\Component\Enrich\Query\AscendantCategoriesInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var NormalizerInterface */
    private $versionNormalizer;

    /** @var ImageNormalizer */
    private $imageNormalizer;

    /** @var VersionManager */
    private $versionManager;

    /** @var AttributeConverterInterface */
    private $localizedConverter;

    /** @var ConverterInterface */
    private $productValueConverter;

    /** @var FormProviderInterface */
    private $formProvider;

    /** @var LocaleRepositoryInterface */
    private $localeRepository;

    /** @var EntityWithFamilyValuesFillerInterface */
    private $entityValuesFiller;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var VariantProductRatioInterface */
    private $variantProductRatioQuery;

    /** @var VariantNavigationNormalizer */
    private $navigationNormalizer;

    /** @var ImageAsLabel */
    private $imageAsLabel;

    /** @var AscendantCategoriesInterface */
    private $ascendantCategoriesQuery;

    /** @var NormalizerInterface */
    private $incompleteValuesNormalizer;

    /** @var UserContext */
    private $userContext;

    /** @var NormalizerInterface */
    private $parentAssociationsNormalizer;

    /** @var MissingAssociationAdder */
    private $missingAssociationAdder;

    /**
     * @param NormalizerInterface                       $normalizer
     * @param NormalizerInterface                       $versionNormalizer
     * @param VersionManager                            $versionManager
     * @param ImageNormalizer                           $imageNormalizer
     * @param AttributeConverterInterface               $localizedConverter
     * @param ConverterInterface                        $productValueConverter
     * @param FormProviderInterface                     $formProvider
     * @param LocaleRepositoryInterface                 $localeRepository
     * @param EntityWithFamilyValuesFillerInterface     $entityValuesFiller
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param VariantNavigationNormalizer               $navigationNormalizer
     * @param VariantProductRatioInterface              $variantProductRatioQuery
     * @param ImageAsLabel                              $imageAsLabel
     * @param AscendantCategoriesInterface              $ascendantCategoriesQuery
     * @param NormalizerInterface                       $incompleteValuesNormalizer
     * @param UserContext                               $userContext
     * @param MissingAssociationAdder                   $missingAssociationAdder
     * @param NormalizerInterface                       $parentAssociationsNormalizer
     */
    public function __construct(
        NormalizerInterface $normalizer,
        NormalizerInterface $versionNormalizer,
        VersionManager $versionManager,
        ImageNormalizer $imageNormalizer,
        AttributeConverterInterface $localizedConverter,
        ConverterInterface $productValueConverter,
        FormProviderInterface $formProvider,
        LocaleRepositoryInterface $localeRepository,
        EntityWithFamilyValuesFillerInterface $entityValuesFiller,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        VariantNavigationNormalizer $navigationNormalizer,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        AscendantCategoriesInterface $ascendantCategoriesQuery,
        NormalizerInterface $incompleteValuesNormalizer,
        UserContext $userContext,
        MissingAssociationAdder $missingAssociationAdder,
        NormalizerInterface $parentAssociationsNormalizer
    ) {
        $this->normalizer = $normalizer;
        $this->versionNormalizer = $versionNormalizer;
        $this->versionManager = $versionManager;
        $this->imageNormalizer = $imageNormalizer;
        $this->localizedConverter = $localizedConverter;
        $this->productValueConverter = $productValueConverter;
        $this->formProvider = $formProvider;
        $this->localeRepository = $localeRepository;
        $this->entityValuesFiller = $entityValuesFiller;
        $this->attributesProvider = $attributesProvider;
        $this->navigationNormalizer = $navigationNormalizer;
        $this->variantProductRatioQuery = $variantProductRatioQuery;
        $this->imageAsLabel = $imageAsLabel;
        $this->ascendantCategoriesQuery = $ascendantCategoriesQuery;
        $this->incompleteValuesNormalizer = $incompleteValuesNormalizer;
        $this->userContext = $userContext;
        $this->parentAssociationsNormalizer = $parentAssociationsNormalizer;
        $this->missingAssociationAdder = $missingAssociationAdder;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductModelInterface $productModel
     */
    public function normalize($productModel, $format = null, array $context = []): array
    {
        $this->missingAssociationAdder->addMissingAssociations($productModel);
        $this->entityValuesFiller->fillMissingValues($productModel);

        $normalizedProductModel = $this->normalizer->normalize($productModel, 'standard', $context);

        $normalizedProductModel['values'] = $this->localizedConverter->convertToLocalizedFormats(
            $normalizedProductModel['values'],
            $context
        );

        $normalizedProductModel['family'] = $productModel->getFamilyVariant()->getFamily()->getCode();
        $normalizedProductModel['values'] = $this->productValueConverter->convert($normalizedProductModel['values']);

        $oldestLog = $this->versionManager->getOldestLogEntry($productModel);
        $newestLog = $this->versionManager->getNewestLogEntry($productModel);

        $created = null !== $oldestLog ?
            $this->versionNormalizer->normalize(
                $oldestLog,
                'internal_api',
                ['timezone' => $this->userContext->getUserTimezone()]
            ) : null;
        $updated = null !== $newestLog ?
            $this->versionNormalizer->normalize(
                $newestLog,
                'internal_api',
                ['timezone' => $this->userContext->getUserTimezone()]
            ) : null;

        $levelAttributes = [];
        foreach ($this->attributesProvider->getAttributes($productModel) as $attribute) {
            $levelAttributes[] = $attribute->getCode();
        }

        $axesAttributes = [];
        foreach ($this->attributesProvider->getAxes($productModel) as $attribute) {
            $axesAttributes[] = $attribute->getCode();
        }

        $normalizedProductModel['parent_associations'] = $this->parentAssociationsNormalizer
            ->normalize($productModel, $format, $context);

        $normalizedFamilyVariant = $this->normalizer->normalize($productModel->getFamilyVariant(), 'standard');

        $variantProductCompletenesses = $this->variantProductRatioQuery->findComplete($productModel);
        $closestImage = $this->imageAsLabel->value($productModel);

        $scopeCode = $context['channel'] ?? null;

        $normalizedProductModel['meta'] = [
                'variant_product_completenesses' => $variantProductCompletenesses->values(),
                'family_variant'            => $normalizedFamilyVariant,
                'form'                      => $this->formProvider->getForm($productModel),
                'id'                        => $productModel->getId(),
                'created'                   => $created,
                'updated'                   => $updated,
                'model_type'                => 'product_model',
                'attributes_for_this_level' => $levelAttributes,
                'attributes_axes'           => $axesAttributes,
                'image'                     => $this->normalizeImage($closestImage, $context),
                'variant_navigation'        => $this->navigationNormalizer->normalize($productModel, $format, $context),
                'ascendant_category_ids'    => $this->ascendantCategoriesQuery->getCategoryIds($productModel),
                'required_missing_attributes' => $this->incompleteValuesNormalizer->normalize($productModel, $format, $context),
                'level'                     => $productModel->getVariationLevel(),
            ] + $this->getLabels($productModel, $scopeCode) + $this->getAssociationMeta($productModel);

        return $normalizedProductModel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface && in_array($format, $this->supportedFormat);
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string|null           $scopeCode
     *
     * @return array
     */
    private function getLabels(ProductModelInterface $productModel, string $scopeCode = null): array
    {
        $labels = [];

        foreach ($this->localeRepository->getActivatedLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $productModel->getLabel($localeCode, $scopeCode);
        }

        return ['label' => $labels];
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    protected function getAssociationMeta(ProductModelInterface $productModel)
    {
        $meta = [];
        $associations = $productModel->getAssociations();

        foreach ($associations as $association) {
            $associationType = $association->getAssociationType();
            $meta[$associationType->getCode()]['groupIds'] = array_map(
                function ($group) {
                    return $group->getId();
                },
                $association->getGroups()->toArray()
            );
        }

        return ['associations' => $meta];
    }

    /**
     * @param ValueInterface|null $data
     * @param array               $context
     *
     * @return array|null
     */
    private function normalizeImage(?ValueInterface $data, array $context = []): ?array
    {
        return $this->imageNormalizer->normalize($data, $context['locale']);
    }
}
