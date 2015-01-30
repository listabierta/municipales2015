<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidateStep6Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			     ->add('govern_priorities', 'choice', array(
			     		'multiple' => TRUE,
			     		'expanded' => TRUE,
			     		'choices' => array(
			     				'1' => 'Mejorar las funciones de gobierno del municipio',
			     				'2' => 'Mejorar la seguridad ciudadana',
			     				'3' => 'Luchar contra la pobreza y la exclusión social',
			     				'4' => 'Mejorar la atención sanitaria',
			     				'5' => 'Mejorar la gestión educativa y cultural',
			     				'6' => 'Atender a los problemas de vivienda, urbanismo e infraestructuras',
			     				'7' => 'Mejorar la sostenibilidad del municipio',
			     				'8' => 'Invertir en fomento del empleo',
			     				'9' => 'Aumentar la participación ciudadana en la gestión municipal',
			     				'10' => 'Mejorar los servicios de transporte',
			     				'11' => 'Invertir en actividades industriales, agrícolas o comerciales',
			     				'12' => 'Invertir en Investigación',
			     				'13' => 'Gestionar mejor la deuda y las operaciones financieras',
			     		),
			     		'data_class' => NULL,
			     		'constraints' => array(
			     				new Assert\Count(array(
						            'max'        => 3,
						            'minMessage' => 'You must specify at least one email',
						            'maxMessage' => 'Sólo puedes seleccionar hasta {{ limit }} opciones como máximo',
						        ))
			     		)
			     ))
	            ->add('continue', 'submit', array('attr' => array('class' => 'submit')));     
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidate_step6';
    }
}