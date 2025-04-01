<?php

namespace App\Form;

use App\Entity\Entretien;
use App\Entity\Evaluation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreEva')
            ->add('noteTechnique')
            ->add('noteSoftSkills')
            ->add('commentaire')
            ->add('dateEvaluation', null, [
                'widget' => 'single_text',
            ])
            ->add('scorequiz')
            ->add('questions')
            ->add('entretien', EntityType::class, [
                'class' => Entretien::class,
                'choice_label' => 'idEntretien',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,
        ]);
    }
}
