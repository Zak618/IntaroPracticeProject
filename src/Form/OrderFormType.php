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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // dd($options);
        $builder
            
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('patronymic', TextType::class)
            ->add('email')
            ->add('phone')//, TelType::class)
            ->add('address')
            ->add('payment', ChoiceType::class, [
                'choices'  => $options['data']['delivery'],
            ])
            ->add('delivery', ChoiceType::class, [
                'choices'  => $options['data']['payment'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // $resolver->setDefaults([
        //     'data_class' => Client::class,
        // ]);
    }
}
