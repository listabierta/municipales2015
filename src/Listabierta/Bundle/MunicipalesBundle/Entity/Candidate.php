<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Candidate
 */
class Candidate implements AdvancedUserInterface, \Serializable
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
    private $phone;


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
     * @return Candidate
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
     * @return Candidate
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
     * @return Candidate
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
     * @return Candidate
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
     * Set phone
     *
     * @param string $phone
     * @return Candidate
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
     * @return Candidate
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
     * @return Candidate
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
     * @var string
     */
    private $motivation_text;

    /**
     * @var array
     */
    private $town_activities_explanation;

    /**
     * @var string
     */
    private $additional_info;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * Set job_experience
     *
     * @param array $jobExperience
     * @return Candidate
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
     * @return Candidate
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
     * @return Candidate
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
     * @return Candidate
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
     * Set motivation_text
     *
     * @param string $motivationText
     * @return Candidate
     */
    public function setMotivationText($motivationText)
    {
        $this->motivation_text = $motivationText;

        return $this;
    }

    /**
     * Get motivation_text
     *
     * @return string 
     */
    public function getMotivationText()
    {
        return $this->motivation_text;
    }

    /**
     * Set town_activities_explanation
     *
     * @param array $townActivitiesExplanation
     * @return Candidate
     */
    public function setTownActivitiesExplanation($townActivitiesExplanation)
    {
        $this->town_activities_explanation = $townActivitiesExplanation;

        return $this;
    }

    /**
     * Get town_activities_explanation
     *
     * @return array 
     */
    public function getTownActivitiesExplanation()
    {
        return $this->town_activities_explanation;
    }

    /**
     * Set additional_info
     *
     * @param string $additionalInfo
     * @return Candidate
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additional_info = $additionalInfo;

        return $this;
    }

    /**
     * Get additional_info
     *
     * @return string 
     */
    public function getAdditionalInfo()
    {
        return $this->additional_info;
    }
    /**
     * @var integer
     */
    private $admin_id;


    /**
     * Set admin_id
     *
     * @param integer $adminId
     * @return Candidate
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
    	return array('ROLE_USER');
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
    
}
