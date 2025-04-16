<?php
namespace App\Form;

use App\Entity\Tache;
use App\Enum\StatusTache;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class Tache1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statut', EnumType::class, [
                'class' => StatusTache::class,
                'choice_label' => fn(StatusTache $type) => $type->getLabel(),
                'label' => 'Status',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
            'validation_groups' => ['status_update'],
        ]);
    }
}