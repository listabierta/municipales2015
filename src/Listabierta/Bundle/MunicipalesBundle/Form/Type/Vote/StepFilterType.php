<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type\Vote;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class StepFilterType extends AbstractType
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
			     ->add('academic_level_option', 'choice', array(
				        'choices' => array('no' => 'No', 'yes' => 'Si'),
        				'preferred_choices' => array('no'),
        				'data' => 'no',
				        'multiple' => false,
				        'expanded' => true,
				        'required' => false,
			     		'empty_value' => false
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
			     ->add('languages_option', 'choice', array(
			     		'choices' => array('no' => 'No', 'yes' => 'Si'),
			     		'preferred_choices' => array('no'),
			     		'data' => 'no',
			     		'multiple' => false,
			     		'expanded' => true,
			     		'required' => false,
			     		'empty_value' => false
			     ))
			     ->add('job_experience', 'choice', array(
			     		'multiple' => TRUE,
			     		'expanded' => TRUE,
			     		'choices' => array(
			     				'0' => 'No considero importante su experiencia laboral previa', // (no aparece el resto de opciones)
			     				'1' => 'Actividades físicas o deportivas',
			     				'2' => 'Administración o gestión',
			     				'3' => 'Agraria o pesquera',
			     				'4' => 'Arte o artesanía',
			     				'5' => 'Comercio y marketing',
			     				'6' => 'Edificación, obra civil',
			     				'7' => 'Energía o agua',
			     				'8' => 'Mecánica, electricidad o electrónica',
			     				'9' => 'Hostelería y turismo',
			     				'10' => 'Informática o comunicaciones',
			     				'11' => 'Industria',
			     				'12' => 'Sanidad',
			     				'13' => 'Medio Ambiente',
			     				'14' => 'Educación o servicios a la comunidad',
			     				'15' => 'Transporte',
			     				'16' => 'Finanzas',
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
			     ->add('job_experience_option', 'choice', array(
			     		'choices' => array('no' => 'No', 'yes' => 'Si'),
			     		'preferred_choices' => array('no'),
			     		'data' => 'no',
			     		'multiple' => false,
			     		'expanded' => true,
			     		'required' => false,
			     		'empty_value' => false
			     ))
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
			     ->add('town_activities_option', 'choice', array(
			     		'choices' => array('no' => 'No', 'yes' => 'Si'),
			     		'preferred_choices' => array('no'),
			     		'data' => 'no',
			     		'multiple' => false,
			     		'expanded' => true,
			     		'required' => false,
			     		'empty_value' => false
			     ))
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
			     ->add('govern_priorities_option', 'choice', array(
			     		'choices' => array('no' => 'No', 'yes' => 'Si'),
			     		'preferred_choices' => array('no'),
			     		'data' => 'no',
			     		'multiple' => false,
			     		'expanded' => true,
			     		'required' => false,
			     		'empty_value' => false
			     ))
			     ->add('public_values', 'choice', array(
			     		'multiple' => TRUE,
			     		'expanded' => TRUE,
			     		'choices' => array(
			     				'1' => 'HONESTIDAD',
			     				'2' => 'TRANSPARENCIA',
			     				'3' => 'RESPETO',
			     				'4' => 'RESPONSABILIDAD',
			     				'5' => 'COMPROMISO',
			     				'6' => 'INTEGRIDAD',
			     				'7' => 'IGUALDAD',
			     				'8' => 'TOLERANCIA',
			     				'9' => 'COOPERACIÓN',
			     				'10' => 'SOLIDARIDAD',
			     				'11' => 'JUSTICIA',
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
			     ->add('public_values_option', 'choice', array(
			     		'choices' => array('no' => 'No', 'yes' => 'Si'),
			     		'preferred_choices' => array('no'),
			     		'data' => 'no',
			     		'multiple' => false,
			     		'expanded' => true,
			     		'required' => false,
			     		'empty_value' => false
			     ))
	            ->add('continue', 'submit', array('attr' => array('class' => 'submit')));
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'vote_step_filter';
    }
}