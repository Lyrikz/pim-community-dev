<?php

namespace spec\Pim\Component\Catalog\Validator\Mapping;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pim\Component\Catalog\Validator\ConstraintGuesserInterface;
use Pim\Component\Catalog\Validator\Mapping\ClassMetadataFactory;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

class ProductValueMetadataFactorySpec extends ObjectBehavior
{
    function it_is_a_validator_metadata_factory()
    {
        $this->shouldBeAnInstanceOf(MetadataFactoryInterface::class);
    }

    function let(
        ConstraintGuesserInterface $guesser,
        ClassMetadataFactory $factory
    ) {
        $this->beConstructedWith($guesser, $factory);
    }

    function its_getMetadataFor_method_throws_exception_when_argument_if_not_a_product_value($object)
    {
        $this
            ->shouldThrow(new NoSuchMetadataException())
            ->duringGetMetadataFor($object);
    }

    function it_has_metadata_for_product_value(ValueInterface $value)
    {
        $this->hasMetadataFor($value)->shouldBe(true);
    }

    function it_does_not_have_metadata_for_something_else($object)
    {
        $this->hasMetadataFor($object)->shouldBe(false);
    }

    function it_provides_metadata_for_product_value(
        $guesser,
        $factory,
        ClassMetadata $metadata,
        ValueInterface $value,
        AttributeInterface $attribute,
        Constraint $unique,
        Constraint $validNumber
    ) {
        $factory->createMetadata(Argument::any())->willReturn($metadata);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('myCode');
        $guesser->guessConstraints($attribute)->willReturn([$unique, $validNumber]);

        $unique->getTargets()->willReturn(Constraint::PROPERTY_CONSTRAINT);
        $validNumber->getTargets()->willReturn(Constraint::PROPERTY_CONSTRAINT);

        $metadata->addPropertyConstraint('data', $unique)->shouldBeCalled();
        $metadata->addPropertyConstraint('data', $validNumber)->shouldBeCalled();

        $this->getMetadataFor($value);
    }

    function it_supports_property_constraint(
        $guesser,
        $factory,
        ClassMetadata $metadata,
        ValueInterface $value,
        AttributeInterface $attribute,
        Constraint $property
    ) {
        $factory->createMetadata(Argument::any())->willReturn($metadata);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('myCode');
        $guesser->guessConstraints($attribute)->willReturn([$property]);

        $property->getTargets()->willReturn(Constraint::PROPERTY_CONSTRAINT);

        $this->getMetadataFor($value);
    }

    function it_supports_class_constraint(
        $guesser,
        $factory,
        ClassMetadata $metadata,
        ValueInterface $value,
        AttributeInterface $attribute,
        Constraint $class
    ) {
        $factory->createMetadata(Argument::any())->willReturn($metadata);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('myCode');
        $guesser->guessConstraints($attribute)->willReturn([$class]);

        $class->getTargets()->willReturn(Constraint::CLASS_CONSTRAINT);

        $this->getMetadataFor($value);
    }

    function it_does_not_support_multi_targets_constraint(
        $guesser,
        $factory,
        ClassMetadata $metadata,
        ValueInterface $value,
        AttributeInterface $attribute,
        Constraint $multiTargets
    ) {
        $factory->createMetadata(Argument::any())->willReturn($metadata);

        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('myCode');
        $guesser->guessConstraints($attribute)->willReturn([$multiTargets]);

        $multiTargets->getTargets()->willReturn([Constraint::PROPERTY_CONSTRAINT, Constraint::CLASS_CONSTRAINT]);

        $this
            ->shouldThrow(new \LogicException('No support provided for constraint on many targets'))
            ->duringGetMetadataFor($value);
    }
}
