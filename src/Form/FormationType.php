<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Form\DataTransformer\StringToDateTimeTransformer;

class FormationType extends AbstractType
{

    private StringToDateTimeTransformer $transformer;

    public function __construct(StringToDateTimeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la formation'
            ])
            ->add('description', TextareaType::class)
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en jours)'
            ])
            ->add('dateFormation', DateType::class, [
                'widget' => 'single_text',
                'html5' => true
            ])

            ->add('isFavorite', CheckboxType::class, [
                'label' => 'Formation favorite',
                'required' => false
            ]);
            $builder->get('dateFormation')->addModelTransformer($this->transformer);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
