<?php
namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStepConditionsType;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStepVerifyType;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep2Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep3Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep4Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep5Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep6Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep7Type;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep3Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep4Type;

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
        $this->stepConditionsAction($request);
    }
    
    /**
     * Step for show the conditions for register
     * 
     * @author Ángel Guzmán Maeso <shakaran@gmail.com>
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stepConditionsAction(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	 
    	$form = $this->createForm(new CandidacyStepConditionsType(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step_conditions'),
    			'method' => 'POST',
    		)
    	);
    	 
    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$conditions = $form['conditions']->getData();
    		
    		if(empty($conditions) || $conditions != 'yes')
    		{
    			$form->addError(new FormError('Debes aceptar las condiciones de alta para continuar'));
    			$ok = FALSE;
    		}
    
    		if($ok)
    		{
    			$session->clear();
    			$session->set('conditions', $conditions);
    	   
    			$form2 = $this->createForm(new CandidacyStep1Type(), NULL, array(
    					'action' => $this->generateUrl('municipales_candidacy_step1'),
    					'method' => 'POST',
    			));
    	   
    			$form2->handleRequest($request);
    	   
    			return $this->render('MunicipalesBundle:Candidacy:step1.html.twig', array(
    					'form' => $form2->createView()
    				)
    			);
    		}
    	}
    	 
    	return $this->render('MunicipalesBundle:Candidacy:step_conditions.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step1Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	
    	$conditions = $session->get('conditions');
    	
    	// Check conditions in step 1 for avoid bad usage
    	if(empty($conditions) || $conditions!= 'yes')
    	{
    		return $this->redirect($this->generateUrl('municipales_candidacy_step_conditions'), 301);
    	}
    	
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
    		$username = $form['username']->getData();
    		$password = $form['password']->getData();
    		$email    = $form['email']->getData();
    		$province = $form['province']->getData();
    		$town     = $form['town']->getData();
    		$phone    = $form['phone']->getData();

    		$entity_manager = $this->getDoctrine()->getManager();
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');

    		$admin_username = $admin_candidacy_repository->findOneBy(array('username' => $username));
    		 
    		if(!empty($admin_username))
    		{
    			$form->addError(new FormError('Ya existe un usuario registrado con el nombre de usuario ' . $username));
    			$ok = FALSE;
    		}
    		
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
    			$admin_candidacy->setUsername($username);
    			
    			$factory = $this->get('security.encoder_factory');
    			$encoder = $factory->getEncoder($admin_candidacy);
    			$encodedPassword = $encoder->encodePassword($password, $admin_candidacy->getSalt());

    			$admin_candidacy->setPassword($encodedPassword);
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

    			$session->set('admin_id', $admin_candidacy->getId());
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
	    			$form->addError(new FormError('El número de móvil <b>' . $phone . '</b> aún no ha sido verificado'));
	    			$ok = FALSE;
    			}
    		}
    			
    		if($ok)
    		{
    			$form2 = $this->createForm(new CandidacyStep2Type(), NULL, array(
	    			'action' => $this->generateUrl('municipales_candidacy_step2'),
	    			'method' => 'POST',
    			));
    
    			$form2->handleRequest($request);
    
    			return $this->render('MunicipalesBundle:Candidacy:step2.html.twig', array(
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
    		$program              = $form['program'];
    		$legal_conditions     = $form['legal_conditions'];
    		$recall_term          = $form['recall_term'];
    		$participatory_term   = $form['participatory_term'];
    		$voter_conditions     = $form['voter_conditions'];
    		$technical_constrains = $form['technical_constrains'];
    		
    		$admin_id = $session->get('admin_id', NULL);;
    		$town = $session->get('town', array());;
    		
    		$town_slug = $this->get('slugify')->slugify($town);
    		
    		$documents_path = 'docs/' . $town_slug . '/' . $admin_id;
    		
    		// getMaxFilesize()
    		
    		if($program->isValid())
    		{
    			$program_data = $program->getData();
    		
    			if($program_data->getClientMimeType() !== 'application/pdf')
    			{
    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $program_data->getClientMimeType()));
    				$ok = FALSE;
    			}
    		
    			if($ok)
    			{
    				$program_data->move($documents_path, 'program.pdf');
    			}
    		}
    		else
    		{
    			$form->addError(new FormError('program pdf is not valid: ' . $program_data->getErrorMessage()));
    			$ok = FALSE;
    		}
    		
    		if($legal_conditions->isValid())
    		{
    			$legal_conditions_data = $legal_conditions->getData();
    		
    			if($legal_conditions_data->getClientMimeType() !== 'application/pdf')
    			{
    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $legal_conditions_data->getClientMimeType()));
    				$ok = FALSE;
    			}
    		
    			if($ok)
    			{
    				$legal_conditions_data->move($documents_path, 'legal_conditions.pdf');
    			}
    		}
    		else
    		{
    			$form->addError(new FormError('legal conditions pdf is not valid: ' . $legal_conditions_data->getErrorMessage()));
    			$ok = FALSE;
    		}
    		
    		if($recall_term->isValid())
    		{
    			$recall_term_data = $recall_term->getData();
    		
    			if($recall_term_data->getClientMimeType() !== 'application/pdf')
    			{
    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $recall_term_data->getClientMimeType()));
    				$ok = FALSE;
    			}
    		
    			if($ok)
    			{
    				$recall_term_data->move($documents_path, 'recall_term.pdf');
    			}
    		}
    		else
    		{
    			$form->addError(new FormError('recall term pdf is not valid: ' . $recall_term_data->getErrorMessage()));
    			$ok = FALSE;
    		}

    		if($participatory_term->isValid())
    		{
    			$participatory_term_data = $participatory_term->getData();
    		
    			if($participatory_term_data->getClientMimeType() !== 'application/pdf')
    			{
    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $participatory_term_data->getClientMimeType()));
    				$ok = FALSE;
    			}
    		
    			if($ok)
    			{
    				$participatory_term_data->move($documents_path, 'participatory_term.pdf');
    			}
    		}
    		else
    		{
    			$form->addError(new FormError('participatory term pdf is not valid: ' . $participatory_term_data->getErrorMessage()));
    			$ok = FALSE;
    		}

    		if($voter_conditions->isValid())
    		{
    			$voter_conditions_data = $voter_conditions->getData();
    		
    			if($voter_conditions_data->getClientMimeType() !== 'application/pdf')
    			{
    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $voter_conditions_data->getClientMimeType()));
    				$ok = FALSE;
    			}
    		
    			if($ok)
    			{
    				$voter_conditions_data->move($documents_path, 'voter_conditions.pdf');
    			}
    		}
    		else
    		{
    			$form->addError(new FormError('voter conditions pdf is not valid: ' . $voter_conditions_data->getErrorMessage()));
    			$ok = FALSE;
    		}

    		if($technical_constrains->isValid())
    		{
    			$technical_constrains_data = $technical_constrains->getData();
    		
    			if($technical_constrains_data->getClientMimeType() !== 'application/pdf')
    			{
    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $technical_constrains_data->getClientMimeType()));
    				$ok = FALSE;
    			}
    		
    			if($ok)
    			{
    				$technical_constrains_data->move($documents_path, 'technical_constrains.pdf');
    			}
    		}
    		else
    		{
    			$form->addError(new FormError('technical constrainss pdf is not valid: ' . $technical_constrains_data->getErrorMessage()));
    			$ok = FALSE;
    		}
    		
    		if($ok)
    		{
    			$session->set('program', $program_data->getClientOriginalName());
    			$session->set('legal_conditions', $legal_conditions_data->getClientOriginalName());
    			$session->set('recall_term', $recall_term_data->getClientOriginalName());
    			$session->set('participatory_term', $participatory_term_data->getClientOriginalName());
    			$session->set('voter_conditions', $voter_conditions_data->getClientOriginalName());
    			$session->set('technical_constrains', $technical_constrains_data->getClientOriginalName());

    			$warnings = array();
				
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
    			);
    		}
    	}
    	 
    	return $this->render('MunicipalesBundle:Candidacy:step2.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step3Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    
    	$form = $this->createForm(new CandidacyStep3Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step3'),
    			'method' => 'POST',
    		)
    	);
    
    	$form->handleRequest($request);

    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$from_data    = $form['from']->getData();
    		$to_data      = $form['to']->getData();
    		
    		$now = new \Datetime('NOW');
    		
    		$current_interval = $from_data->diff($now);
    		$current_days = intval($current_interval->format('%a'));
    		
    		if($current_days > 0)
    		{
    			$form->addError(new FormError('No pueden usarse una fecha pasada como fecha inicial'));
    			$ok = FALSE;
    		}
    		
    		$interval = $from_data->diff($to_data);
    		$total_days = intval($interval->format('%a'));
    		
    		if($total_days < 7)
    		{
    			$form->addError(new FormError('El número mínimo de dias entre la fecha inicial y final es 7'));
    			$ok = FALSE;
    		}

    		if($ok)
    		{
    			$session->set('from', $from_data->getTimestamp());
    			$session->set('to', $to_data->getTimestamp());
    			
    			$admin_id = $session->get('admin_id');
    			 
    			$entity_manager = $this->getDoctrine()->getManager();
    			
    			$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    			
    			$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    			 
    			if(empty($admin_candidacy))
    			{
    				throw $this->createNotFoundException('No existe la candidatura de administrador para guardar la dirección ' . $address_slug);
    			}
    			 
    			$admin_candidacy->setFromdate($from_data);
    			$admin_candidacy->setTodate($to_data);
    			 
    			$entity_manager->persist($admin_candidacy);
    			$entity_manager->flush();

    			$form2 = $this->createForm(new CandidacyStep4Type(), NULL, array(
    					'action' => $this->generateUrl('municipales_candidacy_step4'),
    					'method' => 'POST',
    			));
    
    			$form2->handleRequest($request);
    
    			$town = $session->get('town', array());;
    			
    			$default_address_slug = $this->get('slugify')->slugify($town);
    			
    			return $this->render('MunicipalesBundle:Candidacy:step4.html.twig', array(
    					'default_address_slug' => $default_address_slug,
    					'form' => $form2->createView()
    				)
    			);
    		}
    	}
    
    	return $this->render('MunicipalesBundle:Candidacy:step3.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step4Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    
    	$form = $this->createForm(new CandidacyStep4Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step4'),
    			'method' => 'POST',
    		)
    	);

    	//$town = $session->get('town', NULL);
    	$town = 'manzanares';
    	
    	if(empty($town))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada para obtener la ciudad',
    		));
    	}
    	 
    	$default_address_slug = $this->get('slugify')->slugify($town);
    	
    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$address_data = $form['address']->getData();
    		
    		if(empty($address_data))
    		{
    			$form->addError(new FormError('La dirección no puede ser vacía'));
    			$ok = FALSE;
    		}

    		if($ok)
    		{
    			$address_slug = $this->get('slugify')->slugify($address_data);
    			
    			$session->set('address', $address_slug);
    			
    			$admin_id = $session->get('admin_id');
    			
    			$entity_manager = $this->getDoctrine()->getManager();

    			$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    				
    			$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    			
    			if(empty($admin_candidacy))
    			{
    				throw $this->createNotFoundException('No existe la candidatura de administrador para guardar la dirección ' . $address_slug);
    			}
    			
    			$admin_candidacy->setAddress($address_slug);
    			
    			$entity_manager->persist($admin_candidacy);
    			$entity_manager->flush();

    			$form2 = $this->createForm(new CandidacyStep5Type(), NULL, array(
    					'action' => $this->generateUrl('municipales_candidacy_step5'),
    					'method' => 'POST',
    			));
    
    			$form2->handleRequest($request);
    
    			return $this->render('MunicipalesBundle:Candidacy:step5.html.twig', array(
    					'form' => $form2->createView(),
    					'address_slug' => $address_slug,
    				)
    			);
    		}
    	}
    
    	return $this->render('MunicipalesBundle:Candidacy:step4.html.twig', array(
    			'form' => $form->createView(),
    			'default_address_slug' => $default_address_slug,
    			'address_slug' => $default_address_slug,
    	));
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step5Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    
    	$address_slug = $session->get('address', NULL);
    	
    	$form = $this->createForm(new CandidacyStep5Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step6'),
    			'method' => 'POST',
    		)
    	);

    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		if($ok)
    		{
    			$this->step6Action($request);
    		}
    	}
    
    	return $this->render('MunicipalesBundle:Candidacy:step5.html.twig', array(
    			'form' => $form->createView(),
    			'address_slug' => $address_slug,
    	));
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step6Action(Request $request = NULL)
    {
    	$admin_id = NULL;
    	$session = $this->getRequest()->getSession();
    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$securityContext = $this->container->get('security.context');

    	if($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) 
    	{
    		$current_user = $securityContext->getToken()->getUser();
    		
    		if(in_array('ROLE_ADMIN', $current_user->getRoles()))
    		{
    			
    			$admin_id = $current_user->getId();
    		}
    	}
    	else
    	{
    		$admin_id = $session->get('admin_id');
    	}
    	
    	// Fetch all Candidate for this town and admin_id
    	$candidates = array();
    	
    	if(empty($admin_id))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada',
    		));
    	}
    	
    	$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    	$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	
    	if(empty($admin_candidacy))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada',
    		));
    	}
    	
    	$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
    	
    	$candidates = $candidate_repository->findAll(array('admin_id' => $admin_id));
    	
    	$form_step7 = $this->createForm(new CandidacyStep7Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step7'),
    			'method' => 'POST',
    	));
    	 
    	$form_step7->handleRequest($request);
    	
    	$form = $this->createForm(new CandidacyStep6Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step6'),
    			'method' => 'POST',
    	));
    	
    	$form->handleRequest($request);
    	
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		if($ok)
    		{
    			//$this->step6Action($request);
    		}
    	}
    	
    	$town = $admin_candidacy->getTown();
    		
    	$town_slug = $this->get('slugify')->slugify($town);
    	
    	$documents_path = 'docs/' . $town_slug . '/' . $admin_id . '/candidate/';
    	
    	
    	$form_step3 = $this->createForm(new CandidateStep3Type(), NULL, array());
    	$form_step4 = $this->createForm(new CandidateStep4Type(), NULL, array());
    	
    	return $this->render('MunicipalesBundle:Candidacy:step6.html.twig', array(
    			'form' => $form->createView(),
    			'form_step7' => $form_step7->createView(),
    			'candidates' => $candidates,
    			'documents_path' => $documents_path,
    		)
    	);
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step7Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	
    	$candidacy_to_date = $session->get('to', array());
    	
    	$candidacy_finished = $candidacy_to_date <= time();
    	
    	$candidacy_finished = TRUE;
    	
    	$form = $this->createForm(new CandidacyStep7Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step7'),
    			'method' => 'POST',
    	));
    	 
    	$form->handleRequest($request);
    	 
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$total_days = intval($form['total_days']->getData());

    		if($total_days < 7)
    		{
    			$form->addError(new FormError('El número mínimo de dias es 7'));
    			$ok = FALSE;
    		}
    		
    		if($ok)
    		{
    			$session->set('total_days', $total_days);
    			return $this->redirect($this->generateUrl('municipales_candidacy_step8'), 301);
    		}
    	}
    	 
    	return $this->render('MunicipalesBundle:Candidacy:step7.html.twig', array(
    			'form' => $form->createView(),
    			'candidacy_finished' => $candidacy_finished,
    		)
    	);
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step8Action(Request $request = NULL)
    {
    	return $this->render('MunicipalesBundle:Candidacy:step8.html.twig', array('bla' => 'ble'));
    }
}
