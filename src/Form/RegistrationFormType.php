<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('firstname',TextType::class, [

                'required' => true,
                'constraints' => [new Length([
                    'min' => 3,
                    
                ]),
                    new NotBlank([
                        'message' => 'Пожалуйста, введите имя',
                    ]),
    
            ],])
            ->add('lastname',TextType::class, [

                'required' => true,
                'constraints' => [new Length([
                    'min' => 3,
                    
                ]),
                    new NotBlank([
                        'message' => 'Пожалуйста, введите Фамилию',
                    ]),
    
            ],])
            ->add('patronymic',TextType::class, [

                'required' => true,
                'constraints' => [new Length([
                    'min' => 3,
                    
                ]),
                    new NotBlank([
                        'message' => 'Пожалуйста, введите Отчество',
                    ]),
    
            ],])
            //->add('phone')
            ->add('phone', TelType::class,[
                'required' => true,
                'constraints' => [
                    new Length([
                    'min' => 3,
                    
                ]),
                new Regex([
                    'pattern'=> "/^\+79\d{9}$/"
                    
              ]),
                    new NotBlank([
                        'message' => 'Вы пытаетесь ввести недопустимые символы. Введите номер телефона в формате +7**********',
                    ]),
    
            ],

            ])
            //->add('birthday')
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ],
            ])
            ->add('sex', ChoiceType::class, [
                'choices'  => [
                    'Женский' => 2,
                    'Мужской' => 1,
                    
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
