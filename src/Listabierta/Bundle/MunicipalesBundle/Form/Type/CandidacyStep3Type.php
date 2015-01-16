<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidacyStep3Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('from', 'date', array(
        					'required' => true, 
        					'format' => 'yyyy-MM-dd',
        					'data' => new \Datetime('NOW'),
        					'placeholder' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day'),
        					'widget' => 'single_text',
        					'input' => 'datetime',
        					'html5' => TRUE,
        					'constraints' => array(
        						new Assert\NotBlank(),
        					)
        				)
        			)
        		->add('to', 'date', array(
        					'required' => true,
        					'data' => new \Datetime('+7 days'),
        					'format' => 'yyyy-MM-dd',
        					'placeholder' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day'),
        					'widget' => 'single_text',
        					'input' => 'datetime',
        					'html5' => TRUE,
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
        return 'candidacy_step3';
    }
}