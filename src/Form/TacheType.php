<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Tache;
use App\Enum\StatusTache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('dateDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('dateFin', null, [
                'widget' => 'single_text',
            ])
            ->add('statut', EnumType::class, [
                'class' => StatusTache::class,
                'choice_label' => fn(StatusTache $type) => $type->getLabel(),
                'label' => 'statut',
                'placeholder' => 'Sélectionnez un statut',
            ])
            ->add('trelloboardid')
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}
