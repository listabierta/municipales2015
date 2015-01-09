<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep1Type;

class CandidacyController extends Controller
{
    public function indexAction(Request $request)
    {
        $this->step1Action();
    }
    
    public function step1Action(Request $request)
    {
    	$form = $this->createForm(new CandidacyStep1Type());
    	
    	$form->handleRequest($request);
    	
    	if ($form->isValid())
    	{
    		
    	}
    	
    	return $this->render('MunicipalesBundle:Candidacy:step1.html.twig', array('form' => $form->createView()));
    }
}
