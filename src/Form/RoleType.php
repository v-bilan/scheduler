<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\Witness;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('priority')
            ->add('school', CheckboxType::class, ['required' => false])
            ->add('witnesses', EntityType::class, [
                'class' => Witness::class,
                'choice_label' => 'fullName',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
                'attr' => [
                    'size' => 30, 
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('w')
                    ->where('w.active = :active')
                    ->setParameter('active', true)
                    ->orderBy('w.fullName', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}
