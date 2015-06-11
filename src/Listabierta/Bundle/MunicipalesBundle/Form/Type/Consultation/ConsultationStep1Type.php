<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type\Consultation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Listabierta\Bundle\MunicipalesBundle\Validator\Constraints\DNI;

class ConsultationStep1Type extends AbstractType
{
	private $translations = array();
	
	public function __construct($translations = array())
	{
		$this->translations = $translations;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_question', 'choice', array(
				        'choices' => array(
				        		'a' => $this->translations['consultation.step1.first_question_option_A'], 
				        		'b' => $this->translations['consultation.step1.first_question_option_B']
				        ),
        				'preferred_choices' => array('a'),
        				'data' => 'no',
				        'multiple' => false,
				        'expanded' => true,
				        'required' => true,
				    ))
				 ->add('second_question', 'choice', array(
				    		'choices' => array(
				    				'a' => $this->translations['consultation.step1.second_question_option_A'],
				    				'b' => $this->translations['consultation.step1.second_question_option_B']
				    		),
				    		'preferred_choices' => array('a'),
				    		'data' => 'no',
				    		'multiple' => false,
				    		'expanded' => true,
				    		'required' => false,
				    ))
        		->add('continue', 'submit', array('attr' => array('class' => 'submit','style' => 'margin-left:0px')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'consultation_step1';
    }
}