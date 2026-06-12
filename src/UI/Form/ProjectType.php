<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\Entity\ProjectStatus;
use App\Domain\Project\Application\Data\ProjectData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Project Name',
                'attr'  => ['placeholder' => 'Project name'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 2, max: 200),
                ],
            ])
            ->add('customer', EntityType::class, [
                'class'        => Customer::class,
                'label'        => 'Customer',
                'choice_label' => fn(Customer $c) => $c->getCompany()
                    ? $c->getName() . ' (' . $c->getCompany() . ')'
                    : $c->getName(),
                'placeholder'  => '— Select customer —',
                'constraints'  => [new Assert\NotNull()],
            ])
            ->add('status', EnumType::class, [
                'class'        => ProjectStatus::class,
                'label'        => 'Status',
                'choice_label' => fn(ProjectStatus $s) => $s->label(),
            ])
            ->add('websiteUrl', UrlType::class, [
                'label'         => 'Website URL',
                'required'      => false,
                'default_protocol' => 'https',
                'attr'          => ['placeholder' => 'https://client.example.com'],
            ])
            ->add('githubRepository', TextType::class, [
                'label'    => 'GitHub Repository',
                'required' => false,
                'attr'     => ['placeholder' => 'owner/repo'],
            ])
            ->add('budget', NumberType::class, [
                'label'    => 'Monthly Budget (hours)',
                'required' => false,
                'scale'    => 0,
                'attr'     => ['placeholder' => 'e.g. 160'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => ['rows' => 3, 'placeholder' => 'Project description…'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Project',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectData::class,
        ]);
    }
}
