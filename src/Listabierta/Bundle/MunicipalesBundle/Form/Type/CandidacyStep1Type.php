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
        					new Assert\Length(array(
        							'min'        => 2,
        							'max'        => 255,
        							'minMessage' => 'Your first name must be at least {{ limit }} characters long',
        							'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters long',
        					))
        				)))
        	    ->add('lastname', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        							'min'        => 2,
        							'max'        => 255,
        							'minMessage' => 'Your lastname must be at least {{ limit }} characters long',
        							'maxMessage' => 'Your lastname cannot be longer than {{ limit }} characters long',
        					))
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
        					new Assert\Length(array(
        						'min'        => 2,
        						'max'        => 255,
        						'minMessage' => 'Your province must be at least {{ limit }} characters long',
        						'maxMessage' => 'Your province cannot be longer than {{ limit }} characters long',
        					))
        				)))
        	    ->add('town', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        							'min'        => 2,
        							'max'        => 255,
        							'minMessage' => 'Your town must be at least {{ limit }} characters long',
        							'maxMessage' => 'Your town cannot be longer than {{ limit }} characters long',
        					))
        				)))
        	    ->add('phone', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        						'min'        => 9,
        						'max'        => 12,
        						'minMessage' => 'Your phone must be at least {{ limit }} characters long',
        						'maxMessage' => 'Your phone cannot be longer than {{ limit }} characters long',
        						))
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