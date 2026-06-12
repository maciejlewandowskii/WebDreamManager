<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\Domain\Identity\Application\Data\ChangePasswordData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label'       => 'Current Password',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => ['label' => 'New Password'],
                'second_options' => ['label' => 'Confirm New Password'],
                'invalid_message' => 'Passwords do not match.',
                'constraints'    => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters.'),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Change Password'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ChangePasswordData::class]);
    }
}
