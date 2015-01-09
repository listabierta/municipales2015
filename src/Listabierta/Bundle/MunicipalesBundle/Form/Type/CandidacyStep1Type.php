<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidacyStep1Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
        				'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        				)))
        	    ->add('lastname', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        				)))
        	    ->add('dni', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        				)))
        	    ->add('email', 'email', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Email(),
        				)))
        	    ->add('province', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        				)))
        	    ->add('town', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        				)))
        	    ->add('phone', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        				)))
	            ->add('continue', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidacy_step1';
    }
}