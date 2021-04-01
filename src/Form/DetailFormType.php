<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', RegistrationFormType::class, [
                'disabled' => 'email'
            ])
            ->add('profil', ProfilFormType::class, [

            ])
            //->get('profil')->get('DateOfBirth')->setDisabled(true)
            ->get('profil')->get('city')->setDisabled(true)

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
