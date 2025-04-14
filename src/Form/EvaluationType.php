<?php

namespace App\Form;

use App\Entity\Entretien;
use App\Entity\Evaluation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;



class EvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreEva', null, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Titre de l\'évaluation',
                'empty_data' => '' 

            ])
            ->add('noteTechnique', null, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 20,
                    'step' => 0.5
                ],
                'label' => 'Note Technique (/20)',
                'empty_data' => 0.0
            ])
            ->add('noteSoftSkills', null, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 20,
                    'step' => 0.5
                ],
                'label' => 'Note Soft Skills (/20)',
                'empty_data' => 0.0

            ])
            ->add('commentaire', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'label' => 'Commentaire'
            ])
            ->add('dateEvaluation', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'form-control datetimepicker',
                    'data-timezone' => 'Africa/Tunis'
                ],
                'label' => 'Date et heure'
            ])
            ->add('scorequiz', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 10
                ],
                'label' => 'Score Quiz (/10)',
                'required' => false
            ]);
            

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,
            'attr' => ['novalidate' => 'novalidate']
        ]);
    }
}