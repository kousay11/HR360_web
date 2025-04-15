<?php

namespace App\Form;

use App\Entity\Ressource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DecimalType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class RessourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false, // Désactive la validation native du navigateur
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Type 1' => 'type1',
                    'Type 2' => 'type2',
                    'Type 3' => 'type3',
                ],
                'required' => false,
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Disponible' => 'Disponible',
                    'Indisponible' => 'Indisponible',
                ],
                'required' => false,
            ])
            ->add('prix', NumberType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ressource::class,
        ]);
    }
}
