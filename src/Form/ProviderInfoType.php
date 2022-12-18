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

class ProviderInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeStruct', ChoiceType::class,[
                'choices'  => [
                    'Entreprise' => 'Entreprise',
                    'Administration' => 'Administration',
                    'Association' => 'Association',
                    'Particulier' => 'Particulier',
                ],
                'label' => 'Type de structure'
            ])
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
            ->add('isRegular', ChoiceType::class,[
                'choices'  => [
                    'Réguliers' => true,
                    'Ponctuels' => false,
                ],
                'label' => "Type d'enlèvements"
            ])
            ->add('certificateRequestType', EntityType::class,[
                'class' => CertificateRequestType::class,
                'choice_label' => 'name',
                'label' => "Certificat de destruction"
            ])
            ->add('isExistPlan', ChoiceType::class,[
                'label' => "Plan disponible ?",
                'choices'  => [
                    'Non renseigné' => null,
                    'Non' => 0,
                    'Oui' => 1,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provider::class,
        ]);
    }
}
