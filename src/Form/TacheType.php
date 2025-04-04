<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Tache;
use App\Enum\StatusTache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom', null, [
            'attr' => ['class' => 'form-control'],
            'label' => 'Task Name'
        ])
        ->add('description', null, [
            'attr' => [
                'class' => 'form-control task-description-field',
                'rows' => 6,
                'placeholder' => 'Enter detailed task description...'
            ],
            'label' => 'Description'
        ])
        ->add('dateDebut', null, [
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control datepicker'],
            'label' => 'Start Date'
        ])
        ->add('dateFin', null, [
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control datepicker'],
            'label' => 'End Date'
        ]);

    // Only add status field when editing
    if ($options['is_edit']) {
        $builder->add('statut', EnumType::class, [
            'class' => StatusTache::class,
            'choice_label' => fn(StatusTache $type) => $type->getLabel(),
            'label' => 'Status',
            'attr' => ['class' => 'form-control']
        ]);
    }

    
}

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Tache::class,
        'is_edit' => false,
        'projet' => null  // Changed from projet_id to projet
    ]);
}}
