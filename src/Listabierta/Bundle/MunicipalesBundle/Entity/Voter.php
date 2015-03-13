<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Voter
 */
class Voter implements AdvancedUserInterface, \Serializable
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
     * @var string
     */
    private $phone;

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
     * @return Voter
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
     * @return Voter
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
     * @return Voter
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
     * @return Voter
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
     * Set username
     *
     * @param string $username
     * @return Voter
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Voter
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getSalt()
    {
    	return null;
    }
    
    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Voter
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
     * Set phone
     *
     * @param string $phone
     * @return Voter
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
    /**
     * @var integer
     */
    private $academic_level;

    /**
     * @var array
     */
    private $languages;


    /**
     * Set academic_level
     *
     * @param integer $academicLevel
     * @return Voter
     */
    public function setAcademicLevel($academicLevel)
    {
        $this->academic_level = $academicLevel;

        return $this;
    }

    /**
     * Get academic_level
     *
     * @return integer 
     */
    public function getAcademicLevel()
    {
        return $this->academic_level;
    }

    /**
     * Set languages
     *
     * @param array $languages
     * @return Voter
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get languages
     *
     * @return array 
     */
    public function getLanguages()
    {
        return $this->languages;
    }
    /**
     * @var array
     */
    private $job_experience;

    /**
     * @var array
     */
    private $town_activities;

    /**
     * @var array
     */
    private $govern_priorities;

    /**
     * @var array
     */
    private $public_values;


    /**
     * Set job_experience
     *
     * @param array $jobExperience
     * @return Voter
     */
    public function setJobExperience($jobExperience)
    {
        $this->job_experience = $jobExperience;

        return $this;
    }

    /**
     * Get job_experience
     *
     * @return array 
     */
    public function getJobExperience()
    {
        return $this->job_experience;
    }

    /**
     * Set town_activities
     *
     * @param array $townActivities
     * @return Voter
     */
    public function setTownActivities($townActivities)
    {
        $this->town_activities = $townActivities;

        return $this;
    }

    /**
     * Get town_activities
     *
     * @return array 
     */
    public function getTownActivities()
    {
        return $this->town_activities;
    }

    /**
     * Set govern_priorities
     *
     * @param array $governPriorities
     * @return Voter
     */
    public function setGovernPriorities($governPriorities)
    {
        $this->govern_priorities = $governPriorities;

        return $this;
    }

    /**
     * Get govern_priorities
     *
     * @return array 
     */
    public function getGovernPriorities()
    {
        return $this->govern_priorities;
    }

    /**
     * Set public_values
     *
     * @param array $publicValues
     * @return Voter
     */
    public function setPublicValues($publicValues)
    {
        $this->public_values = $publicValues;

        return $this;
    }

    /**
     * Get public_values
     *
     * @return array 
     */
    public function getPublicValues()
    {
        return $this->public_values;
    }
    /**
     * @var integer
     */
    private $admin_id;


    /**
     * Set admin_id
     *
     * @param integer $adminId
     * @return Voter
     */
    public function setAdminId($adminId)
    {
        $this->admin_id = $adminId;

        return $this;
    }

    /**
     * Get admin_id
     *
     * @return integer 
     */
    public function getAdminId()
    {
        return $this->admin_id;
    }
    /**
     * @var array
     */
    private $vote_info;


    /**
     * Set vote_info
     *
     * @param array $voteInfo
     * @return Voter
     */
    public function setVoteInfo($voteInfo)
    {
        $this->vote_info = $voteInfo;

        return $this;
    }

    /**
     * Get vote_info
     *
     * @return array 
     */
    public function getVoteInfo()
    {
        return $this->vote_info;
    }
}
