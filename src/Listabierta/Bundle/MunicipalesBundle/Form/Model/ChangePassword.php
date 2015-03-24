<?php 
namespace Listabierta\Bundle\MunicipalesBundle\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword
{
    /**
     * @SecurityAssert\UserPassword(
     *     message = "Wrong value for your current password"
     * )
     */
     protected $oldPassword;

    /**
     * @Assert\Length(
     *     min = 6,
     *     minMessage = "Password should by at least 6 chars long"
     * )
     */
     protected $newPassword;
     
     
     public function getOldPassword()
     {
     	return $this->oldPassword;
     }
     
	 public function getNewPassword()
     {
     	return $this->newPassword;
     }
     
     public function setOldPassword($oldPassword = NULL)
     {
     	$this->oldPassword = $oldPassword;
     }
     
     public function setNewPassword($newPassword = NULL)
     {
     	$this->newPassword = $newPassword;
     }
}