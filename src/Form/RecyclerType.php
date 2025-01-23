<?php

namespace App\Form;

use App\Entity\Recycler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecyclerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label' => 'Nom',
            ])
            ->add('address', TextType::class,[
                'label' => 'Adresse'
            ])
            ->add('zipCode', TextType::class,[
                'label' => 'Code Postal'
            ])
            ->add('city', TextType::class,[
                'label' => 'Ville'
            ])
            ->add('attachment', ChoiceType::class,[
                'choices'  => [
                    'Vertou' => 'Vertou',
                    'Saint-Nazaire' => 'Saint-Nazaire',
                ],
                'label' => 'Rattaché à'
            ])
            ->add('commercialContactName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('commercialContactPhone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('commercialContactMail', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('contactName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('contactTelOne', TextType::class, [
                'label' => 'Téléphone 1',
                'required' => false,
            ])
            ->add('contactTelTwo', TextType::class, [
                'label' => 'Téléphone 2',
                'required' => false,
            ])
            ->add('contactMail', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recycler::class,
        ]);
    }
}
