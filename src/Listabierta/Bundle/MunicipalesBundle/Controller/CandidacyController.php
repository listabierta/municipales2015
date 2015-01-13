<?php
namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStepVerifyType;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep2Type;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;
use Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy;

use Symfony\Component\Form\FormError;

/**
 * CandidacyController
 * 
 * @author Ángel Guzmán Maeso <shakaran@gmail.com>
 */
class CandidacyController extends Controller
{
	/**
	 * 
	 * @param Request $request
	 */
    public function indexAction(Request $request = NULL)
    {
        $this->step1Action($request);
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    		$entity_manager = $this->getDoctrine()->getManager();
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		 
    		$admin_email = $admin_candidacy_repository->findOneBy(array('email' => $email));
    		 
    		if(!empty($admin_email))
    		{
    			$form->addError(new FormError('Ya existe un usuario registrado con el email ' . $email));
    			$ok = FALSE;
    		}
    		
    		$admin_phone = $admin_candidacy_repository->findOneBy(array('phone' => $phone));
    		 
    		if(!empty($admin_phone))
    		{
    			$form->addError(new FormError('Ya existe un usuario registrado con el teléfono ' . $phone));
    			$ok = FALSE;
    		}
    		
    		if($ok)
    		{
    			$entity_manager = $this->getDoctrine()->getManager();
    			
    			// Store info in database AdminCandidacy
    			$admin_candidacy = new AdminCandidacy();
    			$admin_candidacy->setName($name);
    			$admin_candidacy->setLastname($lastname);
    			$admin_candidacy->setDni($dni);
    			$admin_candidacy->setEmail($email);
    			$admin_candidacy->setProvince($province);
    			$admin_candidacy->setTown($town);
    			$admin_candidacy->setPhone($phone);
    			
    			$entity_manager->persist($admin_candidacy);
    			$entity_manager->flush();
    			
    			// Store email and phone in database as pending PhoneVerified without timestamp
    			$phone_verified = new PhoneVerified();
    			$phone_verified->setPhone($phone);
    			$phone_verified->setEmail($email);
    			$phone_verified->setTimestamp(0);

    			$entity_manager->persist($phone_verified);
    			$entity_manager->flush();
    			
	    		$session->clear();
	    		$session->set('name', $name);
	    		$session->set('lastname', $lastname);
	    		$session->set('dni', $dni);
	    		$session->set('email', $email);
	    		$session->set('province', $province);
	    		$session->set('town', $town);
	    		$session->set('phone', $phone);
	    		
	    		$warnings = array();
	    		
	    		$form2 = $this->createForm(new CandidacyStepVerifyType(), NULL, array(
	    				'action' => $this->generateUrl('municipales_candidacy_step_verify'),
	    				'method' => 'POST',
	    		));
	    		
	    		$form2->handleRequest($request);
	    		
	    		return $this->render('MunicipalesBundle:Candidacy:step_verify.html.twig', array(
	    				'warnings' => $warnings,
	    				'errors' => $form->getErrors(),
	    				'form' => $form2->createView()
	    			)
	    		);
    		}
    	}
    	
    	return $this->render('MunicipalesBundle:Candidacy:step1.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stepVerifyAction(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    
    	$form = $this->createForm(new CandidacyStepVerifyType(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step_verify'),
    			'method' => 'POST',
    		)
    	);
    
    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$phone = $session->get('phone', array());
    		
    		if(empty($phone))
    		{
    			$form->addError(new FormError('El número de móvil no esta presente. ¿Sesión caducada?'));
    			$ok = FALSE;
    		}
    		
    		$email = $session->get('email', array());
    		if(empty($email))
    		{
    			$form->addError(new FormError('El email no esta presente. ¿Sesión caducada?'));
    			$ok = FALSE;
    		}

    		if($ok)
    		{
    			$entity_manager = $this->getDoctrine()->getManager();
    			$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
    			
    			$phone_status = $phone_verified_repository->findOneBy(array('phone' => $phone, 'email' => $email));
    			
    			if(empty($phone_status) || $phone_status->getTimestamp() == 0)
    			{
	    			$form->addError(new FormError('El número de móvil aún no ha sido verificado'));
	    			$ok = FALSE;
    			}
    		}
    			
    		if($ok)
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
    	}
    
    	return $this->render('MunicipalesBundle:Candidacy:step_verify.html.twig', array(
    			'form' => $form->createView(),
    			'errors' => $form->getErrors(),
    	));
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step2Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	 
    	$form = $this->createForm(new CandidacyStep2Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step2'),
    			'method' => 'POST',
    	)
    	);
    	 
    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$program    = $form['program']->getData();
    		
    		if($ok)
    		{
    			$session->set('program', $program);

    	   
    			$warnings = array();
				/*
    			$form2 = $this->createForm(new CandidacyStep3Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step3'),
    			'method' => 'POST',
    			));
    	   
    			$form2->handleRequest($request);
    	   
    			return $this->render('MunicipalesBundle:Candidacy:step3.html.twig', array(
    					'warnings' => $warnings,
    					'errors' => $form->getErrors(),
    					'form' => $form2->createView()
    			)
    			);*/
    		}
    	}
    	 
    	return $this->render('MunicipalesBundle:Candidacy:step1.html.twig', array(
    			'form' => $form->createView(),
    			'errors' => $form->getErrors(),
    	));
    }
}
