<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TownStep4Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			     ->add('town_activities', 'choice', array(
			     		'multiple' => TRUE,
			     		'expanded' => TRUE,
			     		'choices' => array(
			     				'0' => 'No considero que deba tener ninguna experiencia previa relacionada con el desempeño municipal',
			     				'1' => 'He tenido experiencia en la mejora del funcionamiento organizativo de entidades',
			     				'2' => 'He participado en la elaboración de planes de protección civil o seguridad ciudadana',
			     				'3' => 'He contribuido a la lucha contra la pobreza o la exclusión social',
			     				'4' => 'He gestionado actividades de atención sanitaria',
			     				'5' => 'He gestionado actividades educativas o culturales',
			     				'6' => 'He gestionado planes de infraestructura, vivienda o urbanismo',
			     				'7' => 'He gestionado actividades de fomento de la sostenibilidad o protección medioambiental',
			     				'8' => 'He gestionado actividades de fomento del empleo',
			     				'9' => 'He gestionado actividades de fomento de la transparencia, la participación ciudadana o la igualdad de género',
			     				'10' => 'He gestionado actividades colectivas en el campo industrial o agrícola',
			     				'11' => 'He gestionado actividades colectivas en el campo de las tecnologías de la información o la comunicación',
			     				'12' => 'He gestionado actividades colectivas de investigación o desarrollo',
			     				'13' => 'He gestionado actividades colectivas en el campo del cooperativismo',
			     				'14' => 'He gestionado eficazmente presupuestos superiores al millón de euros',
			     				'15' => 'He tenido experiencia de trabajo internacional',
			     				'16' => 'He realizado funciones de representación o portavocía de colectivos',	
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
        return 'town_step4';
    }
}