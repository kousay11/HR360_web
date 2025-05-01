<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;


class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', EmailType::class)

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'options' => [
                    'attr' => [
                        'class' => 'form-control password-field',
                        'autocomplete' => 'new-password', // Amélioration UX
                    ],
                ],
                'required' => true,
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe.',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                            'max' => 255,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                            'message' => 'Le mot de passe doit contenir au moins 1 majuscule, 1 chiffre et 1 caractère spécial (@$!%*?&).',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ])

            ->add('image', FileType::class, [
                'label' => 'Photo de profil',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner une image'
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une image'
                    ]),
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, JPEG, PNG ou WEBP)',
                        'maxSizeMessage' => 'La taille maximale autorisée est de 2Mo'
                    ])
                ],
                'attr' => ['class' => 'form-control-file']
            ])


            ->add('salaire', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le salaire est obligatoire'
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le salaire doit être un nombre valide'
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 1000000,
                        'notInRangeMessage' => 'Le salaire doit être compris entre {{ min }} et {{ max }}',
                        'invalidMessage' => 'Le salaire doit être un nombre valide'
                    ]),
                    new Assert\PositiveOrZero([
                        'message' => 'Le salaire ne peut pas être négatif'
                    ])
                ],
                'attr' => [
                    'min' => 0,
                    'step' => 0.01
                ]
            ])
            ->add('poste', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La poste est obligatoire'
                    ]),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le poste ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le poste ne peut contenir que des lettres, espaces et tirets'
                    ])
                ]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
