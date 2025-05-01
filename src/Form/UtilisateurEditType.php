<?php

// src/Form/UtilisateurEditType.php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UtilisateurEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'Entrez le nom',
                    'class' => 'form-control'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'Entrez le prénom',
                    'class' => 'form-control'
                ]
            ])

            ->add('email', EmailType::class)
            ->add('image', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WEBP)',
                    ])
                ],
                'attr' => ['class' => 'form-control-file']
            ])
            ->add('salaire', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'Le salaire doit être un nombre valide'
                    ]),
                    new Assert\NotBlank([
                        'message' => 'Le salaire est obligatoire'
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
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le poste ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\NotBlank([
                        'message' => 'La poste est obligatoire'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le poste ne peut contenir que des lettres, espaces et tirets'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
