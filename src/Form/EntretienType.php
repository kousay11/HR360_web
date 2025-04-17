<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\Entretien;
use App\Enum\Statut;
use App\Enum\TypeEnt;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Component\Form\Extension\Core\Type\DateType;


class EntretienType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'novalidate' => 'novalidate'
                ],
                'required' => true,
                'empty_data' => null,
                'invalid_message' => 'la date entretien est obligatoire'
            ])
            ->add('heure', TextType::class, [
                'label' => 'Heure',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'HH:MM',
                    'pattern' => null, // Enlève le pattern HTML5
                    'novalidate' => 'novalidate',
                    'invalid_message' => 'heure entretien est obligatoire'

                ]
            ])           
            ->add('type', EnumType::class, [
                'class' => TypeEnt::class,
                'choice_label' => fn(TypeEnt $type) => $type->getLabel(),
                'label' => 'Type',
                'placeholder' => 'Sélectionnez un type',
                'attr' => [
                    'class' => 'form-control entretien-type-selector',
                    'onchange' => "toggleFields(this.value)"
                ]
            ])
            ->add('statut', EnumType::class, [
                'class' => Statut::class,
                'choice_label' => fn(Statut $statut) => $statut->getLabel(),
                'label' => 'Statut',
                'placeholder' => 'Sélectionnez une statut',
            ])
            
            ->add('localisation', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'localisation',
                'empty_data' => '' 
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entretien::class,
            'attr' => ['novalidate' => 'novalidate'] // Désactive toute la validation HTML5

        ]);
    }
}
