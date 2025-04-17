<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\Offre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('cv', FileType::class, [
            'label' => 'CV (PDF file)',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File([
                    'mimeTypes' => ['application/pdf'],
                    'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide',
                ]),
                new NotBlank(['message' => 'Un CV est requis'])
            ],
        ])
        ->add('lettreMotivation', FileType::class, [
            'label' => 'Lettre de motivation (PDF file)',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File([
                    'mimeTypes' => ['application/pdf'],
                    'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide',
                ]),
                new NotBlank(['message' => 'Une lettre de motivation est requise'])
            ],
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description complémentaire',
            'constraints' => [
                new NotBlank(['message' => 'Veuillez ajouter une description']),
                new Length([
                    'min' => 50,
                    'minMessage' => 'La description doit contenir au moins {{ limit }} caractères'
                ])
            ],
            'attr' => ['rows' => 5]
        ])
    ;
}
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
        ]);
    }
}
