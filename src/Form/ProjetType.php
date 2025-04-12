<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProjetType extends AbstractType
{
    // src/Form/ProjetType.php
public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom', null, [
            'label' => 'Project Name',
            'empty_data' => '',
            'attr' => [
                'placeholder' => 'Enter project name',       
            ],
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'empty_data' => '',
            'attr' => [
                'placeholder' => 'Describe the project details',
                'rows' => 4
            ]
        ])
        ->add('dateDebut',  DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            'html5' => true,
            'format' => 'yyyy-MM-dd',
            'empty_data' => null,
            'attr' => [
                'class' => 'form-control datepicker',
                'placeholder' => 'YYYY-MM-DD'
            ],
            'invalid_message' => 'Please enter a valid date (YYYY-MM-DD)'
        ])
        ->add('dateFin', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            'html5' => true,
            'format' => 'yyyy-MM-dd',
            'empty_data' => null,
            'attr' => [
                'class' => 'form-control datepicker',
                'placeholder' => 'YYYY-MM-DD'
            ],
            'invalid_message' => 'Please enter a valid date (YYYY-MM-DD)'
        ])
    ;
}
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $groups = ['Default'];
                
                // Field-specific group activation
                if ($data && !empty($data->getNom())) {
                    $groups[] = 'not_blank_nom';
                }
                if ($data && !empty($data->getDescription())) {
                    $groups[] = 'not_blank_description';
                }
                
                return $groups;
            },
        ]);
    }
}
