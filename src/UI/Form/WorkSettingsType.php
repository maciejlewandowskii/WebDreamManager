<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\Domain\Identity\Application\Data\WorkSettingsData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/** @extends AbstractType<mixed> */
final class WorkSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('workingHoursPerDay', IntegerType::class, [
                'label'       => 'Working Hours per Day',
                'attr'        => ['min' => 1, 'max' => 24, 'placeholder' => '8'],
                'constraints' => [new Assert\NotBlank(), new Assert\Range(min: 1, max: 24)],
            ])
            ->add('save', SubmitType::class, ['label' => 'Save Preferences'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => WorkSettingsData::class]);
    }
}
