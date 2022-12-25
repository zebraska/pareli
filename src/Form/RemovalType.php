<?php

namespace App\Form;

use App\Entity\Provider;
use App\Entity\Removal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemovalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
              ->add('Provider', EntityType::class,[
            'class' => Provider::class,
            'choice_label' => 'name',
            'label' => "fournisseur"
                                      
            ]) 
             ->add('dateRequest', DateType::class,[
                'widget' => 'single_text',
                'label' => "Date de la demande"
            ])
            ->add('comment', TextareaType::class,[
                'label' => "Instructions d'enlèvements ponctuelles",
                'required' => false
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Removal::class,
        ]);
    }
}
