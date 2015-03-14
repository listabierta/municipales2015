<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidateStep3Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('academic_level', 'choice', array(
			     	'choices' => array(
			     			'1' => 'Sin estudios',
			     			'2' => 'Estudios primarios o equivalentes', 
			     			'3' => 'Enseñanza general secundaria, 1er ciclo',
			     			'4' => 'Enseñanza Profesional de 2º grado, 2º ciclo',
			     			'5' => 'Enseñanza general secundaria, 2º ciclo',
			     			'6' => 'Enseñanzas profesionales superiores',
			     			'7' => 'Estudios universitarios o equivalentes',
			     			'8' => 'Máster universitario o doctorado',
			     	),
			     ))
			     ->add('languages', 'choice', array(
			     		'multiple' => TRUE,
			     		'expanded' => TRUE,
			     		'choices' => array(
			     				'1' => 'Inglés',
			     				'2' => 'Otro idioma europeo',
			     				'3' => 'Otro idioma no europeo',
			     				'4' => 'Catalá',
			     				'5' => 'Galego',
			     				'6' => 'Euskara',
			     				'7' => 'Valenciá',
			     		),
			     ))
	            ->add('continue', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidate_step3';
    }
}