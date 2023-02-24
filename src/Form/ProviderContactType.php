<?php

namespace App\Form;

use App\Entity\CertificateRequestType;
use App\Entity\Provider;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProviderContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('removalContactName', TextType::class, [
                'label' => '1. Nom',
                'required' => false,
            ])
            ->add('removalContactPhone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('removalContactMail', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('removalContactNameTwo', TextType::class, [
                'label' => '2. Nom',
                'required' => false,
            ])
            ->add('removalContactPhoneTwo', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('certificateContactMail', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provider::class,
        ]);
    }
}
