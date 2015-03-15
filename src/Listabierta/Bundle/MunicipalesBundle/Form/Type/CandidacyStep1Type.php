<?php

namespace Listabierta\Bundle\MunicipalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Listabierta\Bundle\MunicipalesBundle\Validator\Constraints\DNI;
use Listabierta\Bundle\MunicipalesBundle\Entity\ProvinceRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CandidacyStep1Type extends AbstractType
{
	
	private $provinces_data = array();
	
	public function __construct($provinces_data)
	{
		// Flatten the results array
		$result = array();
		foreach($provinces_data as $province)
		{
			$result[$province['id']] = $province['name'];
		}
		
		$this->provinces_data = $result;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
        				'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        							'min'        => 2,
        							'max'        => 255,
        							'minMessage' => 'Your first name must be at least {{ limit }} characters long',
        							'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters long',
        					))
        				)))
        	    ->add('lastname', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        							'min'        => 2,
        							'max'        => 255,
        							'minMessage' => 'Your lastname must be at least {{ limit }} characters long',
        							'maxMessage' => 'Your lastname cannot be longer than {{ limit }} characters long',
        					))
        				)))
        	    ->add('dni', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new DNI(),
        				)))
        		->add('username', 'text', array(
        				'required' => true,
        				'constraints' => array(
        						new Assert\NotBlank(),
        						new Assert\Length(array(
        								'min'        => 2,
        								'max'        => 255,
        								'minMessage' => 'Your username must be at least {{ limit }} characters long',
        								'maxMessage' => 'Your username cannot be longer than {{ limit }} characters long',
        						))
        				)))
        		->add('password', 'password', array(
        				'required' => true,
        				'constraints' => array(
        						new Assert\NotBlank(),
        						new Assert\Length(array(
        								'min'        => 8,
        								'max'        => 255,
        								'minMessage' => 'Your password must be at least {{ limit }} characters long',
        								'maxMessage' => 'Your password cannot be longer than {{ limit }} characters long',
        						))
        				)))
        	    ->add('email', 'email', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Email(),
        				)))
        	    ->add('province', 'choice', array(
        	    		'choices' => $this->provinces_data,
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        						'min'        => 2,
        						'max'        => 255,
        						'minMessage' => 'Your province must be at least {{ limit }} characters long',
        						'maxMessage' => 'Your province cannot be longer than {{ limit }} characters long',
        					))
        				)))
        	    ->add('town', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        							'min'        => 2,
        							'max'        => 255,
        							'minMessage' => 'Your town must be at least {{ limit }} characters long',
        							'maxMessage' => 'Your town cannot be longer than {{ limit }} characters long',
        					))
        				)))
        	    ->add('phone', 'text', array(
        	    		'required' => true, 
        				'constraints' => array(
        					new Assert\NotBlank(),
        					new Assert\Length(array(
        						'min'        => 9,
        						'max'        => 12,
        						'minMessage' => 'Your phone must be at least {{ limit }} characters long',
        						'maxMessage' => 'Your phone cannot be longer than {{ limit }} characters long',
        						))
        				)))
	            ->add('continue', 'submit');
    }
	
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'candidacy_step1';
    }
}