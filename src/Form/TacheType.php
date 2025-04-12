<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Tache;
use App\Enum\StatusTache;
use DateTimeInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Form\DataTransformer\NullableDateTransformer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'empty_data' => '',
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Nom de la tâche'
            ])
            ->add('description', TextareaType::class, [
                'empty_data' => '',
                'attr' => [
                    'class' => 'form-control task-description-field',
                    'rows' => 4,
                ],
                'label' => 'Description'
            ])
            ->add('dateDebut', DateType::class, [
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
            ]);

        if ($options['is_edit']) {
            $builder->add('statut', EnumType::class, [
                'class' => StatusTache::class,
                'choice_label' => fn(StatusTache $type) => $type->getLabel(),
                'label' => 'Statut',
                'attr' => ['class' => 'form-control']
            ]);
        }
        
        
    $builder->get('dateFin')
        ->addModelTransformer(new NullableDateTransformer());
    }

public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Tache::class,
        'is_edit' => false,
        'projet' => null,
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
}}
