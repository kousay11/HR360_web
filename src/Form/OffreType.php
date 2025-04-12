<?php

namespace App\Form;

use App\Entity\Offre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Type;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le titre ne peut pas être vide']),
                    new Length([
                        'min' => 5,
                        'max' => 100,
                        'minMessage' => 'Le titre doit avoir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => ['class' => 'form-control'],
                'empty_data' => '' // Pour éviter l'erreur "null given"
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La description ne peut pas être vide']),
                    new Length([
                        'min' => 20,
                        'minMessage' => 'La description doit avoir au moins {{ limit }} caractères'
                    ])
                ],
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'empty_data' => '' // Pour éviter l'erreur "null given"
            ])
            ->add('datePublication', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de publication est obligatoire'
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'Veuillez entrer une date valide pour la publication'
                    ])
                ],
                'label' => 'Date de publication',
                'required' => false,
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'empty_data' => null,
                'attr' => [
                    'class' => 'form-control datepicker',
                    'placeholder' => 'YYYY-MM-DD HH:MM'
                ],
                'invalid_message' => 'Veuillez entrer une date valide pour la publication'
            ])
            ->add('dateExpiration', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date d\'expiration est obligatoire'
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'Veuillez entrer une date valide pour l\'expiration'
                    ]),
                    new GreaterThan([
                        'propertyPath' => 'parent.all[datePublication].data',
                        'message' => 'La date d\'expiration doit être après la date de publication'
                    ])
                ],
                'format' => 'yyyy-MM-dd',
                'empty_data' => null,
                'required' => false,
                'html5' => false,
                'attr' => [
                    'class' => 'form-control datepicker',
                    'placeholder' => 'YYYY-MM-DD HH:MM'
                ],
                'label' => 'Date d\'expiration',
                'invalid_message' => 'Veuillez entrer une date valide pour l\'expiration'
            ]);
    }            

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}