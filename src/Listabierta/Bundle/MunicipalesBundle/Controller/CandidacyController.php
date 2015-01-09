<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep2Type;

use Symfony\Component\Form\FormError;

class CandidacyController extends Controller
{
    public function indexAction(Request $request = NULL)
    {
        $this->step1Action($request);
    }
    
    public function step1Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	
    	$form = $this->createForm(new CandidacyStep1Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step1'),
			    'method' => 'POST',
    			)
    	);
    	
    	$form->handleRequest($request);

    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$name     = $form['name']->getData();
    		$lastname = $form['lastname']->getData();
    		$dni      = $form['dni']->getData();
    		$email    = $form['email']->getData();
    		$province = $form['province']->getData();
    		$town     = $form['town']->getData();
    		$phone    = $form['phone']->getData();
    		
    		if(!empty($phone))
    		{
    			// @todo
    			//PhoneVerified::check()
    			//$form->addError(new FormError('El número de móvil aún no ha sido verificado'));
    			//$ok = FALSE;
    		}
    		
    		if($ok)
    		{
	    		$session->getFlashBag()->clear();
	    		$session->getFlashBag()->set('name', $name);
	    		$session->getFlashBag()->set('lastname', $lastname);
	    		$session->getFlashBag()->set('dni', $dni);
	    		$session->getFlashBag()->set('email', $email);
	    		$session->getFlashBag()->set('province', $province);
	    		$session->getFlashBag()->set('town', $town);
	    		$session->getFlashBag()->set('phone', $phone);
	    		
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
    	}
    	
    	return $this->render('MunicipalesBundle:Candidacy:step1.html.twig', array(
    			'form' => $form->createView(),
    			'errors' => $form->getErrors(),
    	));
    }
}
