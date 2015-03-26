<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ModifyVotePointsSystemType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('vote0', 'number', array(
        					'required' => true, 
        					'precision' => 3,
        					'constraints' => array(
        						new Assert\NotBlank(),
        					)
        				)
        			)
        		->add('vote1', 'number', array(
        					'required' => true,
        					'precision' => 3,
        					'constraints' => array(
        							new Assert\NotBlank(),
        					)
        				)
        		)
        		->add('vote2', 'number', array(
        					'required' => true,
        					'precision' => 3,
        					'constraints' => array(
        							new Assert\NotBlank(),
        					)
        				)
        		)
        		->add('vote3', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)        		
        		->add('vote4', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)
        		->add('vote5', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)
        		->add('vote6', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)
        		->add('vote7', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)
        		->add('vote8', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)
        		->add('vote9', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)
        		->add('vote10', 'number', array(
        				'required' => true,
        				'precision' => 3,
        				'constraints' => array(
        						new Assert\NotBlank(),
        				)
        		)
        		)
        		->add('continue', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'modify_vote_points_system_type';
    }
}