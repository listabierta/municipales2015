<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidacyStep7Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('total_days', 'text', array(
        				'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank()
        				)))
        		->add('start_candidacy', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidacy_step_7';
    }
}