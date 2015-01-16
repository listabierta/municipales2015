<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep2Type;

class TownController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$this->step1Action($request);
	}
	
	public function vote1Action($town = NULL, Request $request = NULL)
	{
		$form = $this->createForm(new TownStep1Type(), NULL, array(
				'action' => $this->generateUrl('municipales_candidacy_step1'),
				'method' => 'POST',
		)
		);
		 
		$form->handleRequest($request);
		
		if ($form->isValid())
		{

		}

		return $this->render('MunicipalesBundle:Town:step1.html.twig', array(
				'town' => $town, 
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}

	public function vote2Action($town = NULL, Request $request = NULL)
	{
		$form = $this->createForm(new TownStep1Type(), NULL, array(
				'action' => $this->generateUrl('municipales_candidacy_step1'),
				'method' => 'POST',
		)
		);
			
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
	
		}
	
		return $this->render('MunicipalesBundle:Town:step1.html.twig', array(
				'town' => $town,
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}
	
	public function resultsAction($town = NULL, Request $request = NULL)
	{
		return $this->render('MunicipalesBundle:Town:step_results.html.twig', array(
				'town' => $town,
		));
	}	
	
}
