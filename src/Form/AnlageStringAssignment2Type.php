<?php

namespace App\Form;

use App\Entity\Anlage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnlageStringAssignment2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $anlageWithAssignments = $options['anlageWithAssignments'];

        $builder
            ->add('anlage', EntityType::class, [
                'class' => Anlage::class,
                'choice_label' => function (Anlage $anlage) use ($anlageWithAssignments) {
                    $lastUploadDate = $anlage->getLastUploadDate();
                    $dateStr = $lastUploadDate ? $lastUploadDate->format('d-m-Y H:i:s') : 'never';
                    $hasAssignments = isset($anlageWithAssignments[$anlage->getAnlId()]);
                    $arrow = $hasAssignments ? '🔵' : '';
                    return sprintf("%s  %s  %s", $anlage->getAnlId(), $dateStr, $arrow);
                },

            ])
            ->add('file', FileType::class)
            ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'anlageWithAssignments' => null // Option pour les données
        ]);
    }
}
