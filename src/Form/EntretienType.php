<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\Entretien;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntretienType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idEntretien')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('heure')
            ->add('type')
            ->add('statut')
            ->add('lienmeet')
            ->add('localisation')
            ->add('idCandidature', EntityType::class, [
                'class' => Candidature::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entretien::class,
        ]);
    }
}
