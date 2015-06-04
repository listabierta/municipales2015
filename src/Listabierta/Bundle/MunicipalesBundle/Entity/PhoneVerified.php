<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhoneVerified
 * 
 * @ORM\Entity
 * @ORM\Table(name="phone_verified")
 */
class PhoneVerified
{
	const MODE_CENSUS_USER = 1;
	
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $phone;
    
    /**
     * @var string
     */
    private $email;

    /**
     * @var integer
     */
    private $timestamp;


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
     * Set phone
     *
     * @param string $phone
     * @return PhoneVerified
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
     * Set email
     *
     * @param string $email
     * @return PhoneVerified
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
     * Set timestamp
     *
     * @param integer $timestamp
     * @return PhoneVerified
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    /**
     * @var integer
     */
    private $mode;


    /**
     * Set mode
     *
     * @param integer $mode
     * @return PhoneVerified
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get mode
     *
     * @return integer 
     */
    public function getMode()
    {
        return $this->mode;
    }
}
