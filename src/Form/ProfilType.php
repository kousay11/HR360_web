<?php


namespace App\Form;

use App\Entity\User;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'empty_data' => '',
                'attr' => ['class' => 'form-control']
            ])

            ->add('competence', TextareaType::class, [
                'label' => 'Compétences',
                'required' => true,
                'attr' => ['class' => 'form-control', 'rows' => 3],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner vos compétences',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Vos compétences doivent faire au moins {{ limit }} caractères',
                    ]),
                ],
            ])

            ->add('image', FileType::class, [
                'label' => 'Photo de profil',
                'required' => true,
                'mapped' => false,
                'attr' => ['class' => 'form-control-file']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
