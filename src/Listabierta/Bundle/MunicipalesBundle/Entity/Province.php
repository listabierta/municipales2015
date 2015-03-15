<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Province
 */
class Province
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
     * @return Province
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
     * @var integer
     */
    private $community_id;


    /**
     * Set community_id
     *
     * @param integer $communityId
     * @return Province
     */
    public function setCommunityId($communityId)
    {
        $this->community_id = $communityId;

        return $this;
    }

    /**
     * Get community_id
     *
     * @return integer 
     */
    public function getCommunityId()
    {
        return $this->community_id;
    }
}
