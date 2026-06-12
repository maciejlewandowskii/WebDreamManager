<?php

declare(strict_types=1);

namespace App\UI\Form\Admin;

use App\Domain\Authorization\Application\Data\RoleData;
use App\Domain\Authorization\Entity\Permission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $permissionChoices = [];
        foreach (Permission::cases() as $permission) {
            $permissionChoices[$permission->value] = $permission->value;
        }

        $builder
            ->add('name', TextType::class, [
                'constraints' => [new NotBlank()],
                'attr'        => ['placeholder' => 'Role name'],
            ])
            ->add('permissions', ChoiceType::class, [
                'choices'  => $permissionChoices,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label'    => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RoleData::class,
        ]);
    }
}
