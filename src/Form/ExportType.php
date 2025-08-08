<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Від',
                'required' => true,
                'html5' => false,
                'attr' => ['class' => 'datepicker'],
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'label' => 'До',
                'required' => true,
                'html5' => false,
                'attr' => ['class' => 'datepicker'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
