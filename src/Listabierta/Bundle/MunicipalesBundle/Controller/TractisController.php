<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Listabierta\Bundle\MunicipalesBundle\Lib\tractis\SymfonyTractisApi;

class TractisController extends Controller
{
	/**
	 * Tractis TSA sign example
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function signAction(Request $request)
	{
		// Create an API Key here: https://www.tractis.com/webservices/tsa/apikeys
		$tractis_api_identifier = $this->container->getParameter('tractis_api_identifier');
		$tractis_api_secret     = $this->container->getParameter('tractis_api_secret');
		
		// Create a vote example data
		$admin_id = 1;
		$voter_id = 2;
		 
		$vote_info = array();
		$vote_info['admin_id'] = $admin_id;
		$vote_info['voter_id'] = $voter_id;
		$vote_info['candidates'] = array(0 => 1, 1 => 5);
		
		// Create a fake vote example data (altered)
		$fake_vote_info = $vote_info;
		$fake_vote_info['voter_id'] = 3;
		
		// Fetch the chain sign TSA file
		$tsa_cert_chain_file = $this->container->get('kernel')->locateResource('@MunicipalesBundle/Lib/tractis/chain.txt');

		// Init the Symfony Tractis TSA Api
		$symfony_tractis_api = new SymfonyTractisApi($tractis_api_identifier, $tractis_api_secret, $tsa_cert_chain_file);
		
		// Sign a valid vote
		$response = $symfony_tractis_api::sign(serialize($vote_info));
		
		// Check response data
	    if(empty($response))
    	{
	    	 return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
	    	 'error' => 'Error en el firmado de voto TSA. Respuesta vacía',
	    	 ));
    	}
    	else 
    	{
    		echo 'TSA sign performed succesfully<br>';
    	}
    	
    	if(empty($response['response_string']))
    	{
	    	 return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
	    	 'error' => 'Error en el firmado de voto TSA. Respuesta con cadena vacía',
	    	 ));
    	}
    	else
    	{
    		echo 'Response string (base64 encoded vote): ' . $response['response_string'] . '<br>';
    	}
    	
    	if(empty($response['response_time']))
    	{
	    	 return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
	    	 'error' => 'Error en el firmado de voto TSA. Respuesta con tiempo vacía',
	    	 ));
    	}
    	else 
    	{
    		echo 'Response timestamp vote: ' . $response['response_time'] . ' (' . date('d-m-Y H:i:s') . ')<br>';
    	}
    	
    	// Fetch the data response if valid
    	$response_string = $response['response_string'];
    	$response_time   = $response['response_time'];
    	
    	// Verify the result
    	$result = $symfony_tractis_api::validate(serialize($vote_info), $response_string, $response_time);
    	
    	echo 'Validating real vote: ' . ($result == TRUE ? '<span style="color:green">OK</span>' : '<span style="color:red">KO</span>') . '<br />';
    	
    	$result = $symfony_tractis_api::validate(serialize($fake_vote_info), $response_string, $response_time);
    	 
    	echo 'Validating altered (fake) vote: ' . ($result == TRUE ? '<span style="color:green">OK</span>' : '<span style="color:red">KO</span>') . '<br />';
    	
		return new Response('', 200);
	}
}
