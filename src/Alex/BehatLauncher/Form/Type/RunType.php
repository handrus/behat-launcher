<?php

namespace Alex\BehatLauncher\Form\Type;

use Alex\BehatLauncher\Behat\Project;
use Alex\BehatLauncher\Behat\ProjectList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RunType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $props = $builder->create('properties', 'form', array('mapped' => false));

        foreach ($options['project']->getProperties() as $property) {
            $props->add(
                $property->getName(),
                $property->getFormType(),
                array_merge($property->getFormOptions(), array('mapped' => false))
            );
        }

        $builder
            ->add('title', 'text', array('required' => false))
            ->add('features', 'behat_launcher_features', array(
                'project' => $options['project'],
                'mapped' => false
            ))
            ->add($props)
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $run  = $event->getData();
                $form = $event->getForm();

                // properties
                foreach ($form->get('properties') as $sub) {
                    $name = $sub->getName();
                    $value = $sub->getData();
                    $run->setProperty($name, $value);
                }

                // features
                $run->createUnits($form->get('features')->getData());
            })
        ;

        $builder->add('submit', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'project' => null,
            'data_class' => 'Alex\BehatLauncher\Behat\Run'
        ));

        $resolver->setRequired(array('project'));
        $resolver->setAllowedTypes(array('project' => 'Alex\BehatLauncher\Behat\Project'));
    }

    public function getName()
    {
        return 'behat_launcher_run';
    }
}
