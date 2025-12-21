<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Bezeichnung (z.B. Home, Arbeit)'])
            ->add('street', TextType::class, ['label' => 'Straße'])
            ->add('postCode', TextType::class, ['label' => 'PLZ'])
            ->add('city', TextType::class, ['label' => 'Ort'])
            ->add('lat', TextType::class, ['label' => 'Breitengrad'])
            ->add('lng', TextType::class, ['label' => 'Längengrad'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
