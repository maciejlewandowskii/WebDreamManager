<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\Domain\Project\Entity\Project;
use App\Domain\TimeTracking\Application\Data\TimeRecordData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class TimeRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr'  => ['placeholder' => 'What did you work on?'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 2, max: 300),
                ],
            ])
            ->add('project', EntityType::class, [
                'class'        => Project::class,
                'label'        => 'Project',
                'choice_label' => 'name',
                'placeholder'  => '— Select project —',
                'constraints'  => [new Assert\NotNull()],
            ])
            ->add('date', DateType::class, [
                'label'  => 'Date',
                'widget' => 'single_text',
                'input'  => 'datetime_immutable',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('spentHours', NumberType::class, [
                'label'   => 'Spent Hours',
                'scale'   => 2,
                'attr'    => ['min' => 0, 'step' => 0.25, 'placeholder' => '0'],
                'constraints' => [new Assert\PositiveOrZero()],
            ])
            ->add('estimatedHours', NumberType::class, [
                'label'    => 'Estimated Hours',
                'required' => false,
                'scale'    => 2,
                'attr'     => ['min' => 0, 'step' => 0.25, 'placeholder' => 'Optional'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 3, 'placeholder' => 'Details about the work done…'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Record',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TimeRecordData::class,
        ]);
    }
}
