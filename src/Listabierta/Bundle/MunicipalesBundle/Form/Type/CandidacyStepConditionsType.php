<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Listabierta\Bundle\MunicipalesBundle\Validator\Constraints\DNI;

class CandidacyStepConditionsType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('conditions', 'choice', array(
				        'choices' => array('yes' => 'SI', 'no' => 'NO'),
        				'preferred_choices' => array('no'),
        				'data' => 'no',
				        'multiple' => false,
				        'expanded' => true,
				        'required' => true,
				    ))
        		->add('continue', 'submit', array('attr' => array('class' => 'submit','style' => 'margin-left:0px')));
    }
	
    // 		<span>{{ form_widget(form.conditions, 'candidacy.step_conditions.accept'|trans) }}</span>
	//	<span>{{ form_errors(form.conditions) }}</span>
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidacy_step_conditions';
    }
}