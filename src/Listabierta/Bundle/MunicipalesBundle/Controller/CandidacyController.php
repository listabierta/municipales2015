<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep2Type;

class CandidacyController extends Controller
{
    public function indexAction(Request $request)
    {
        $this->step1Action();
    }
    
    public function step1Action(Request $request)
    {
    	$form = $this->createForm(new CandidacyStep1Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step1'),
			    'method' => 'POST',
    			)
    	);
    	
    	$form->handleRequest($request);

    	if ($form->isValid())
    	{
    		$warnings = array();
    		
    		$form2 = $this->createForm(new CandidacyStep2Type(), NULL, array(
    				'action' => $this->generateUrl('municipales_candidacy_step2'),
    				'method' => 'POST',
    		));
    		
    		$form2->handleRequest($request);
    		
    		return $this->render('MunicipalesBundle:Candidacy:step2.html.twig', array(
    				'warnings' => $warnings,
    				'errors' => $form->getErrors(),
    				'form' => $form2->createView()
    			)
    		);
    	}
    	
    	return $this->render('MunicipalesBundle:Candidacy:step1.html.twig', array(
    			'form' => $form->createView(),
    			'errors' => $form->getErrors(),
    	));
    }
}
