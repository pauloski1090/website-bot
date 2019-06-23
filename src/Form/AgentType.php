<?php

namespace App\Form;

use App\Entity\Agent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nickname')
            ->add('real_name')
            ->add('lat', NumberType::class, array (
                'required' => true,
                'scale' => 7,
                'attr' => array(
                    'min' => -90,
                    'max' => 90,
                    'step' => 0.0000001,
                ),
            ))
            ->add('lon', NumberType::class, array (
                'required' => true,
                'scale' => 7,
                'attr' => array(
                    'min' => -90,
                    'max' => 90,
                    'step' => 0.0000001,
                ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agent::class,
        ]);
    }
}
