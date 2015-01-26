<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidacyStep2Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('program', 'file', array(
        					'required' => true, 
        					'data_class' => NULL,
        					'constraints' => array(
        						new Assert\NotBlank(),
        					)
        				)
        			)
        		->add('legal_conditions', 'file', array(
        					'required' => true,
        					'data_class' => NULL,
        					'constraints' => array(
        							new Assert\NotBlank(),
        					)
        				)
        			)
        		->add('recall_term', 'file', array(
        					'required' => true,
        					'data_class' => NULL,
        					'constraints' => array(
        							new Assert\NotBlank(),
        					)
        				)
        			)
        		->add('participatory_term', 'file', array(
        					'required' => true,
        					'data_class' => NULL,
        					'constraints' => array(
        							new Assert\NotBlank(),
        					)
        				)
        			)
        		->add('voter_conditions', 'file', array(
        					'required' => true,
        					'data_class' => NULL,
        					'constraints' => array(
        							new Assert\NotBlank(),
        					)
        				)
        			)
        		->add('technical_constrains', 'file', array(
        					'required' => true,
        				    'data_class' => NULL,
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
        return 'candidacy_step2';
    }
}