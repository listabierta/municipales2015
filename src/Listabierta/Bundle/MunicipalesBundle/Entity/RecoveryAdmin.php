<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecoveryAdmin
 */
class RecoveryAdmin
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $adminId;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
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
     * Set adminId
     *
     * @param integer $adminId
     * @return RecoveryAdmin
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Get adminId
     *
     * @return integer 
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return RecoveryAdmin
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set timestamp
     *
     * @param string $timestamp
     * @return RecoveryAdmin
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return string 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
