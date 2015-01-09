<?php
namespace Listabierta\Bundle\MunicipalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DNIValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	{
		// Check format
		if (0 === preg_match("/\d{1,8}[a-z]/i", $value))
		{
			$this->context->addViolationAtSubPath('dni', 'El DNI introducido no tiene el formato correcto (entre 1 y 8 números seguidos de una letra, sin guiones y sin dejar ningún espacio en blanco)', array(), NULL);
		
			return;
		}
		
		// Check letter with algorithm
		$dni_number = substr($value, 0, -1);
		$dni_letter  = strtoupper(substr($value, -1));
		if ($dni_letter != substr("TRWAGMYFPDXBNJZSQVHLCKE", strtr($dni_number, "XYZ", "012")%23, 1)) 
		{
			$this->context->addViolationAtSubPath('dni', 'La letra no coincide con el número del DNI. Comprueba que has escrito bien tanto el número como la letra', array(), NULL);
		}
	}
}