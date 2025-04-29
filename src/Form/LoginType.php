<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CsrfTokenType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    // src/Form/LoginType.php
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre email.']),
                    new Email(['message' => 'Veuillez entrer un email valide.']),
                ],
                'attr' => [
                    'placeholder' => 'Email',
                    'autocomplete' => 'email'
                ]
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre mot de passe.']),
                ],
                'attr' => [
                    'placeholder' => 'Mot de passe',
                    'autocomplete' => 'current-password'
                ]
            ])
            ->add('remember_me', CheckboxType::class, [
                'required' => false,
                'label' => 'Se souvenir de moi'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Pas de data_class car on traite directement le tableau $data
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id'   => 'authenticate', // C'est IMPORTANT pour que CsrfTokenBadge valide bien
        ]);
    }
}
