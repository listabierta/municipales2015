<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep1Type;

class CandidateController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$this->step1Action($request);
	}
	
	public function step1Action($address = NULL, Request $request = NULL)
	{
		$form = $this->createForm(new TownStep1Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step1', array('address' => $address)),
				'method' => 'POST',
		)
		);
		 
		$form->handleRequest($request);
		
		if ($form->isValid())
		{

		}

		return $this->render('MunicipalesBundle:Candidate:step1.html.twig', array(
				'address' => $address, 
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}
}
