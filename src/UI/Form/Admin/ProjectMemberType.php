<?php

declare(strict_types=1);

namespace App\UI\Form\Admin;

use App\Domain\Authorization\Application\Data\ProjectMemberData;
use App\Domain\Authorization\Entity\Permission;
use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
final class ProjectMemberType extends AbstractType
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $permissionChoices = [
            'List time records'              => Permission::TimeRecordList->value,
            'View time record detail'        => Permission::TimeRecordView->value,
            'Create time records'            => Permission::TimeRecordCreate->value,
            'Update own time records'        => Permission::TimeRecordUpdate->value,
            'Delete own time records'        => Permission::TimeRecordDelete->value,
            "View all members' records"      => Permission::TimeRecordViewAll->value,
            "Manage all members' records"    => Permission::TimeRecordManageAll->value,
        ];

        $builder
            ->add('user', EntityType::class, [
                'class'        => User::class,
                'choices'      => $this->userRepository->findAll(),
                'choice_label' => 'fullName',
                'placeholder'  => '— Select user —',
                'disabled'     => $options['edit_mode'],
            ])
            ->add('permissions', ChoiceType::class, [
                'choices'  => $permissionChoices,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label'    => 'Project permissions',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectMemberData::class,
            'edit_mode'  => false,
        ]);
    }
}
