<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * AdminCandidacy
 * 
 * @ORM\Entity
 * @ORM\Table(name="admin_candidacy")
 */
class AdminCandidacy implements AdvancedUserInterface, \Serializable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $dni;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $province;

    /**
     * @var string
     */
    private $town;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $address;
    
    /**
     * @var string
     */
    private $to;
    
    /**
     * @var string
     */
    private $from;
    
    public function __construct()
    {
    	$this->isActive = TRUE;
    	$this->roles = new ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return AdminCandidacy
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return AdminCandidacy
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set dni
     *
     * @param string $dni
     * @return AdminCandidacy
     */
    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    /**
     * Get dni
     *
     * @return string 
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return AdminCandidacy
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set province
     *
     * @param string $province
     * @return AdminCandidacy
     */
    public function setProvince($province)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return string 
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set town
     *
     * @param string $town
     * @return AdminCandidacy
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string 
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return AdminCandidacy
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return AdminCandidacy
     */
    public function setAddress($address)
    {
    	$this->address = $address;
    
    	return $this;
    }
    
    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
    	return $this->address;
    }

    /**
     * Set to
     *
     * @param \DateTime $to
     * @return AdminCandidacy
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return \DateTime 
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set from
     *
     * @param \DateTime $from
     * @return AdminCandidacy
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return \DateTime 
     */
    public function getFrom()
    {
        return $this->from;
    }
    /**
     * @var \DateTime
     */
    private $todate;

    /**
     * @var \DateTime
     */
    private $fromdate;


    /**
     * Set todate
     *
     * @param \DateTime $todate
     * @return AdminCandidacy
     */
    public function setTodate($todate)
    {
        $this->todate = $todate;

        return $this;
    }

    /**
     * Get todate
     *
     * @return \DateTime 
     */
    public function getTodate()
    {
        return $this->todate;
    }

    /**
     * Set fromdate
     *
     * @param \DateTime $fromdate
     * @return AdminCandidacy
     */
    public function setFromdate($fromdate)
    {
        $this->fromdate = $fromdate;

        return $this;
    }

    /**
     * Get fromdate
     *
     * @return \DateTime 
     */
    public function getFromdate()
    {
        return $this->fromdate;
    }
    
    /**
     * @inheritDoc
     */
    public function getUsername()
    {
    	return $this->username;
    }
    
    /**
     * @inheritDoc
     */
    public function getSalt()
    {
    	return null;
    }
    
    /**
     * @inheritDoc
     */
    public function getPassword()
    {
    	return $this->password;
    }
    
    /**
     * @inheritDoc
     */
    public function getRoles()
    {
    	return array('ROLE_USER', 'ROLE_ADMIN');
    }
    
    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }
    
    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
    	return serialize(array(
    			$this->id,
    			$this->username,
    			$this->password,
    	));
    }
    
    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
    	list (
    			$this->id,
    			$this->username,
    			$this->password,
    	) = unserialize($serialized);
    }
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var boolean
     */
    private $isActive;


    /**
     * Set username
     *
     * @param string $username
     * @return AdminCandidacy
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return AdminCandidacy
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return AdminCandidacy
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
    
    public function isAccountNonExpired()
    {
    	return true;
    }
    
    public function isAccountNonLocked()
    {
    	return true;
    }
    
    public function isCredentialsNonExpired()
    {
    	return true;
    }
    
    public function isEnabled()
    {
    	return $this->isActive;
    }
    
    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $roles;

    /**
     * Add roles
     *
     * @param  $roles
     * @return User
     */
    public function addRole( $roles)
    {
    	$this->roles[] = $roles;
    
    	return $this;
    }
    
    /**
     * Check if a user is normal/regular user (has role user)
     *
     * @return boolean
     */
    public function isNormalUser()
    {
    	return $this->hasRole('ROLE_USER');
    }
    
    /**
     * Check if a user is admin (has role admin)
     *
     * @return boolean
     */
    public function isAdmin()
    {
    	return $this->hasRole('ROLE_ADMIN');
    }
    
    /**
     * Check if a user is super admin (has role super admin)
     *
     * @return boolean
     */
    public function isSuperAdmin()
    {
    	return $this->hasRole('ROLE_SUPER_ADMIN');
    }
    
    /**
     * Check if a user has a role
     *
     * @param string $role_name
     * @return boolean
     */
    public function hasRole($role_name = NULL)
    {
    	$roles = $this->getRoles();
    
    	foreach($roles as $rol)
    	{
    		if($rol->getName() === $role_name)
    		{
    			return TRUE;
    		}
    	}
    
    	return FALSE;
    }
    
    /**
     * Get all roles names as array
     *
     * @return array
     */
    public function getRolesNames()
    {
    	$roles = $this->getRoles();
    
    	$names = array();
    	foreach($roles as $rol)
    	{
    		$names[] = $rol->getName();
    	}
    
    	return $names;
    }
    
    /**
     * Remove roles
     *
     * @param  $roles
     */
    public function removeRole( $roles)
    {
    	$this->roles->removeElement($roles);
    }
}
