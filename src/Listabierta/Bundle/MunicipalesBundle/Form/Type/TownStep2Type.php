<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TownStep2Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('academic_level', 'choice', array(
			     	'choices' => array(
			     			'0' => 'Me resulta indiferente su nivel académico', // no aparece el resto de opciones
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
			     				'0' => 'No considero necesario que hable idiomas',
			     				'1' => 'Inglés',
			     				'2' => 'Francés',
			     				'3' => 'Alemán',
			     				'4' => 'Italiano',
			     				'5' => 'Chino',
			     				'6' => 'Árabe',
			     				'7' => 'Otro',
			     		),
			     ))
	            ->add('continue', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'town_step2';
    }
}