<?php

declare(strict_types=1);

namespace App\UI\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
final class MoneyCurrencyType extends AbstractType
{
    /** @var array<string, string> */
    public const array CURRENCIES = ['PLN' => 'PLN', 'EUR' => 'EUR', 'USD' => 'USD', 'GBP' => 'GBP'];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label'    => false,
                'required' => $options['required'],
                'currency' => false,
                'scale'    => 2,
                'attr'     => ['placeholder' => '0.00', 'step' => '0.01'],
            ])
            ->add('currency', ChoiceType::class, [
                'label'   => false,
                'choices' => self::CURRENCIES,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
