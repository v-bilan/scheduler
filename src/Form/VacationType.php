<?php

namespace App\Form;

use App\Entity\Vacation;
use App\Entity\Witness;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VacationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'From',
                'required' => true,
                'html5' => false,
                'attr' => ['class' => 'datepicker'],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'To',
                'required' => true,
                'html5' => false,
                'attr' => ['class' => 'datepicker'],
            ])
            ->add('witness', EntityType::class, [
                'class' => Witness::class,
                'choice_label' => 'fullName',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('w')
                        ->orderBy('w.fullName', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vacation::class,
        ]);
    }
}
