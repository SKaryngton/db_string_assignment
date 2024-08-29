<?php

namespace App\Form;

use App\Entity\Anlage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnlageStringAssignmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('anlage', EntityType::class, [
                'class' => Anlage::class,
                'choice_label' => 'id', // Remplacez 'name' par la propriété de Anlage à afficher
            ])
            ->add('database', ChoiceType::class, [
                'choices' => [
                    'PVP Data' => 'default',
                    'PVP Division' => 'division',
                ],
            ])
            ->add('file', FileType::class)
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
