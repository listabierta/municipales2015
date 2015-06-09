<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Consultation
 */
class Consultation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * @var integer
     */
    private $census_user_id;

    /**
     * @var string
     */
    private $token;


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
     * Set data
     *
     * @param array $data
     * @return Consultation
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set census_user_id
     *
     * @param integer $censusUserId
     * @return Consultation
     */
    public function setCensusUserId($censusUserId)
    {
        $this->census_user_id = $censusUserId;

        return $this;
    }

    /**
     * Get census_user_id
     *
     * @return integer 
     */
    public function getCensusUserId()
    {
        return $this->census_user_id;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Consultation
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
}
