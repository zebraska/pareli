<?php

namespace App\Form;

use App\Entity\Container;
use App\Entity\RemovalContainerQuantity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemovalContainerQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'label' => "Quantité"
            ])
            ->add('container', EntityType::class, [
                'class' => Container::class,
                'choice_label' => 'name',
                'label' => "Contenant"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RemovalContainerQuantity::class,
        ]);
    }
}
