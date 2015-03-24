<?php 

namespace Listabierta\Bundle\MunicipalesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangePasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('oldPassword', 'password');
		$builder->add('newPassword', 'repeated', array(
				'type' => 'password',
				'invalid_message' => 'The password fields must match.',
				'required' => true,
				'first_options'  => array('label' => 'Contraseña'),
				'second_options' => array('label' => 'Repite contraseña'),
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'Listabierta\Bundle\MunicipalesBundle\Form\Model\ChangePassword',
		));
	}

	public function getName()
	{
		return 'change_passwd';
	}
}
