<?php
namespace App\Form;

use App\Entity\Equipeemploye;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class EquipeemployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('utilisateur', EntityType::class, [
            'class' => Utilisateur::class,
            'choice_label' => function(Utilisateur $user) {
                return $user->getNom() . ' ' . $user->getPrenom() . ' (' . $user->getEmail() . ')';
            },
            'placeholder' => 'Select an employee',
            'label' => 'Employee to add',
            'query_builder' => function (EntityRepository $er) use ($options) {
    // Get all users
    $qb = $er->createQueryBuilder('u')
        ->where('u.role = :role')
        ->setParameter('role', 'Employe')
        ->orderBy('u.nom', 'ASC');
    
    // If we have a team, exclude its members
    if ($options['equipe']) {
        $subQuery = $er->createQueryBuilder('u2')
            ->select('IDENTITY(ee.utilisateur)')
            ->join('u2.equipeemployes', 'ee')
            ->where('ee.equipe = :equipe')
            ->getDQL();
        
        $qb->andWhere($qb->expr()->notIn('u.id', $subQuery))
           ->setParameter('equipe', $options['equipe']);
    }
    
    return $qb;
},
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Equipeemploye::class,
            'equipe' => null,
        ]);
    }
}