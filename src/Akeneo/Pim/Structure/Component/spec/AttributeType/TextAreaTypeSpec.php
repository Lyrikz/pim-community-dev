<?php

namespace spec\Akeneo\Pim\Structure\Component\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class TextAreaTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeTypes::BACKEND_TYPE_TEXTAREA);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_textarea');
    }
}
