<?php

declare(strict_types=1);

namespace App\UI\Form\Admin;

use App\Domain\Authorization\Application\Data\UserAdminData;
use App\Domain\Authorization\Entity\Role;
use App\Domain\Authorization\Repository\RoleRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/** @extends AbstractType<mixed> */
final class UserManageType extends AbstractType
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isNew = $options['is_new'];

        $builder
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank(), new Email()],
            ])
            ->add('fullName', TextType::class, [
                'label'       => 'Full Name',
                'constraints' => [new NotBlank(), new Length(min: 2, max: 100)],
            ])
            ->add('role', EntityType::class, [
                'class'        => Role::class,
                'choices'      => $this->roleRepository->findAll(),
                'choice_label' => 'name',
                'placeholder'  => '— No role —',
                'required'     => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Active',
                'required' => false,
            ]);

        if (!$isNew) {
            $builder->add('plainPassword', PasswordType::class, [
                'label'       => 'New Password (leave blank to keep current)',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [new Length(min: 8)],
                'attr'        => ['autocomplete' => 'new-password'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserAdminData::class,
            'is_new'     => false,
        ]);
    }
}
