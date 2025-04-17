<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'input', 'placeholder' => 'Email'],
                'label' => 'Email',
                'required' => false, // désactive aussi la validation HTML5 si tu veux
            ])
            ->add('password', PasswordType::class, [
                'attr' => ['class' => 'input', 'placeholder' => 'Mot de passe'],
                'label' => 'Mot de passe',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
