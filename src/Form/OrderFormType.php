<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OrderType extends AbstractType
{
    // private array $deliveryChoices;
    // private array $paymentChoices;

    // public function __construct(array $deliveryChoices, array $paymentChoices)
    // {
    //     $this->deliveryChoices = $deliveryChoices;
    //     $this->paymentChoices = $paymentChoices;
    // }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('patronymic', TextType::class)
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('payment', ChoiceType::class, [
                'choices' => $options['paymentChoices'],
            ])
            ->add('delivery', ChoiceType::class, [
                'choices' => $options['deliveryChoices'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
            'deliveryChoices' => [],
            'paymentChoices' => [],
        ]);
    }
}
