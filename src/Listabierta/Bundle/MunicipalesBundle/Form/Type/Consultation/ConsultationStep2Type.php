<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type\Consultation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Listabierta\Bundle\MunicipalesBundle\Validator\Constraints\DNI;

class ConsultationStep2Type extends AbstractType
{
	private $translations = array();
	
	public function __construct($translations = array())
	{
		$this->translations = $translations;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        		->add('continue', 'submit', array('attr' => array('class' => 'submit','style' => 'margin-left:0px')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'consultation_step2';
    }
}