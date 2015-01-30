<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CandidateStep7Type extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
			     ->add('motivation_text', 'textarea', array(
			     		'attr' => array('class' => 'tinymce', 'style' => 'width:100%;height:200px'),
			     		'required' => FALSE
			     ))
			     ->add('town_activities_explanation', 'textarea', array(
			     		'attr' => array('class' => 'tinymce', 'style' => 'width:100%;height:200px'),
			     		'required' => FALSE
			     ))
			     ->add('additional_info', 'textarea', array(
			     		'attr' => array('class' => 'tinymce', 'style' => 'width:100%;height:200px'),
			     		'required' => FALSE
			     ))
			     ->add('profile_image', 'file', array(
			     		'required' => FALSE,
			     		'data_class' => NULL,
			     		'constraints' => array(
			     				new Assert\File(array('maxSize' =>'1024k')),
			     		)
			     	)
			     )
	            ->add('continue', 'submit', array('attr' => array('class' => 'submit')));     
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidate_step7';
    }
}