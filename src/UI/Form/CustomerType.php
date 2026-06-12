<?php

declare(strict_types=1);

namespace App\UI\Form;

use App\Domain\Customer\Entity\CustomerStatus;
use App\Domain\Customer\Entity\PdfColorMode;
use App\Domain\Customer\Application\Data\CustomerData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full Name',
                'attr'  => ['placeholder' => 'e.g. Jan Kowalski'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 2, max: 200),
                ],
            ])
            ->add('email', EmailType::class, [
                'label'    => 'Email',
                'required' => false,
                'attr'     => ['placeholder' => 'contact@example.com'],
                'constraints' => [new Assert\Email()],
            ])
            ->add('phone', TextType::class, [
                'label'    => 'Phone',
                'required' => false,
                'attr'     => ['placeholder' => '+48 600 000 000'],
            ])
            ->add('company', TextType::class, [
                'label'    => 'Company',
                'required' => false,
                'attr'     => ['placeholder' => 'Company Sp. z o.o.'],
            ])
            ->add('taxId', TextType::class, [
                'label'    => 'Tax ID (NIP)',
                'required' => false,
                'attr'     => ['placeholder' => '000-000-00-00'],
            ])
            ->add('hourlyRate', MoneyCurrencyType::class, [
                'label'    => 'Hourly Rate',
                'required' => false,
            ])
            ->add('status', EnumType::class, [
                'class'        => CustomerStatus::class,
                'label'        => 'Status',
                'choice_label' => fn(CustomerStatus $s) => $s->label(),
            ])
            ->add('pdfColorMode', EnumType::class, [
                'class'        => PdfColorMode::class,
                'label'        => 'PDF Color Mode',
                'choice_label' => fn(PdfColorMode $m) => $m->label(),
            ])
            ->add('notes', TextareaType::class, [
                'label'    => 'Notes',
                'required' => false,
                'attr'     => ['rows' => 3, 'placeholder' => 'Internal notes…'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Customer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerData::class,
        ]);
    }
}
