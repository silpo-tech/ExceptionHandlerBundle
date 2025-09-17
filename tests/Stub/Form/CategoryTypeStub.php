<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests\Stub\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CategoryTypeStub extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'categoryName',
                TextType::class,
                ['required' => true, 'constraints' => [new NotBlank(message: 'Category name empty')]],
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryStub::class,
        ]);
    }
}
