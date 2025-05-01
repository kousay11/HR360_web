<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints as Assert;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-styling', 'placeholder' => 'Email'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire']),
                    new Assert\Email(['message' => 'Veuillez entrer un email valide']),
                ]
            ])
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-styling', 'placeholder' => 'Nom']
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['class' => 'form-styling', 'placeholder' => 'Prénom']
            ])
            ->add('competence', TextType::class, [
                'attr' => ['class' => 'form-styling', 'placeholder' => 'Compétences'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer vos compétences.']),
                    new Assert\Length([
                        'min' => 3,
                        'minMessage' => 'Les compétences doivent contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire']),
                        new Assert\Length([
                            'min' => 6,
                            'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères'
                        ])
                    ]
                ],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
                'mapped' => false
            ])
            ->add('image', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner une image.']),
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/*'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG ou GIF).',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
