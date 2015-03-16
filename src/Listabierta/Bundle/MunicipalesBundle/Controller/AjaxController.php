<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxController extends Controller
{
    public function getMunicipalitiesAction(Request $request, $province_id = NULL)
    {
    	$municipalities = array();
    	$municipalities[0] = 'Elige municipio';
    	
    	$query = "SELECT id, name FROM municipalities_spain WHERE province_id='" . intval($province_id) . "'";
    	
    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$statement = $entity_manager->getConnection()->executeQuery($query);
    	$municipalities_data = $statement->fetchAll();
    	
    	foreach($municipalities_data as $result)
    	{
    		$municipalities[$result['id']] = $result['name'];
    	}
    	
    	return new JsonResponse($municipalities);
    }
}
