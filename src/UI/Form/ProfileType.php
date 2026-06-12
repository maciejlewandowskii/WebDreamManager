<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\Domain\Identity\Application\Data\ProfileData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label'       => 'Full Name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 2, max: 100)],
            ])
            ->add('email', EmailType::class, [
                'label'       => 'Email',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            ])
            ->add('avatarFile', FileType::class, [
                'label'    => 'Profile Photo',
                'required' => false,
                'mapped'   => false,
                'attr'     => ['accept' => 'image/jpeg,image/png,image/webp'],
                'constraints' => [
                    new Assert\Image(maxSize: '2M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp']),
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Save Profile'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ProfileData::class]);
    }
}
