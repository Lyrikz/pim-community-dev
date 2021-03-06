<?php

namespace spec\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class GroupTypeRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'group_type';

        $this->beConstructedWith($em, $classMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\GroupTypeRepository');
    }

    function it_is_a_group_type_repository()
    {
        $this->shouldImplement('Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface');
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }
}
