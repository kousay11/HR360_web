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
use App\Form\DataTransformer\DateToStringTransformer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class FormationType extends AbstractType
{

    private DateToStringTransformer $transformer;


    public function __construct(DateToStringTransformer $transformer)
    {
        $this->transformer = $transformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la formation',
                'empty_data' => '',
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Description de la formation',
                'empty_data' => '',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en jours)',
                'required' => true,
                'empty_data' => '0',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La durée est obligatoire'
                    ])
                ]
            ])
            
            ->add('dateFormation', TextType::class, [
                'label' => 'Date de formation',
                'attr' => [
                    'placeholder' => 'JJ-MM-AAAA',
                    'class' => 'form-control'
                ]
            ])


            ->add('isFavorite', CheckboxType::class, [
                'label' => 'Formation favorite',
                'required' => false
            ]);
            $builder->get('dateFormation')
        ->addModelTransformer(new DateToStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
