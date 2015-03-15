<?php

namespace Listabierta\Bundle\MunicipalesBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * VoterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProvinceRepository extends EntityRepository
{
	public function fetchProvinces()
	{
		$query = "SELECT id, name FROM provinces_spain";
		
		$statement = $this->getEntityManager()->getConnection()->executeQuery($query);
		return $statement->fetchAll();
	}
}
