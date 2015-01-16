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
			     			'0' => 'Me resulta indiferente su nivel académico  (no aparece el resto de opciones)', 
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