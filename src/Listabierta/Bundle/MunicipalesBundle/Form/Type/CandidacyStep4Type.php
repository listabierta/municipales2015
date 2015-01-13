<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Listabierta\Bundle\MunicipalesBundle\Validator\Constraints\DNI;

class CandidacyStep4Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        	->add('address', 'text', array(
        		'required' => true,
        		'constraints' => array(
        				new Assert\NotBlank(),
        				new Assert\Length(array(
        						'min'        => 2,
        						'max'        => 255,
        						'minMessage' => 'Your address must be at least {{ limit }} characters long',
        						'maxMessage' => 'Your address cannot be longer than {{ limit }} characters long',
        				))
        		)))
        	->add('continue', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidacy_step_4';
    }
}