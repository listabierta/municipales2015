<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidateStep2Type extends AbstractType
{
	private static $loaded_files = array();
	
	public function __construct($loaded_files = array())
	{
		self::$loaded_files = $loaded_files;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	if(self::$loaded_files['program'])
    	{
        	$builder->add('program', 'file', array(
        					'required' => FALSE, 
        					'data_class' => NULL,
        					'constraints' => array(
        						new Assert\File(array('maxSize' =>'1024k')),
        					)
        				)
        			);
    	}
    	
    	if(self::$loaded_files['legal_conditions'])
    	{
	        $builder->add('legal_conditions', 'file', array(
	        					'required' => FALSE,
	        					'data_class' => NULL,
	        					'constraints' => array(
	        						new Assert\File(array('maxSize' =>'1024k')),
	        					)
	        				)
	        			);
    	}
    	
    	if(self::$loaded_files['recall_term'])
    	{
        	$builder->add('recall_term', 'file', array(
        					'required' => FALSE,
        					'data_class' => NULL,
        					'constraints' => array(
        						new Assert\File(array('maxSize' =>'1024k')),
        					)
        				)
        			);
    	}
    	
    	if(self::$loaded_files['participatory_term'])
    	{
        	$builder->add('participatory_term', 'file', array(
        					'required' => FALSE,
        					'data_class' => NULL,
        					'constraints' => array(
        						new Assert\File(array('maxSize' =>'1024k')),
        					)
        				)
        			);
    	}
    	
    	if(self::$loaded_files['voter_conditions'])
    	{
	        $builder->add('voter_conditions', 'file', array(
	        					'required' => FALSE,
	        					'data_class' => NULL,
	        					'constraints' => array(
	        						new Assert\File(array('maxSize' =>'1024k')),
	        					)
	        				)
	        			);
    	}
    	
    	if(self::$loaded_files['technical_constrains'])
    	{
	        $builder->add('technical_constrains', 'file', array(
	        					'required' => FALSE,
	        				    'data_class' => NULL,
	        					'constraints' => array(
	        						new Assert\File(array('maxSize' =>'1024k')),
	        					)
	        				)
	        			);
    	}
	    $builder->add('continue', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidate_step2';
    }
}