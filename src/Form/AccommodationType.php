<?php

namespace App\Form;

use App\Entity\Accommodation;
use App\Entity\City;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('address')
            ->add('country')
            ->add('postalCode')
            ->add('typeAccommodation')
            ->add('numberRooms')
            ->add('services')
            ->add('email')
            ->add('img')
            ->add('checkIn', null, [
                'widget' => 'single_text'
            ])
            ->add('checkOut', null, [
                'widget' => 'single_text'
            ])
            ->add('description')
            ->add('hidden')
            ->add('city', EntityType::class, [
                'class' => City::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Accommodation::class,
        ]);
    }
}
