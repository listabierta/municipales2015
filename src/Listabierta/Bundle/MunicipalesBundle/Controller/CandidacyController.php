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
use Listabierta\Bundle\MunicipalesBundle\Form\Type\RecoverPasswordType;

use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;
use Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy;
use Listabierta\Bundle\MunicipalesBundle\Entity\RecoveryAdmin;

use Symfony\Component\Form\FormError;

use Listabierta\Bundle\MunicipalesBundle\Form\ChangePasswordType;
use Listabierta\Bundle\MunicipalesBundle\Form\Model\ChangePassword;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * CandidacyController
 * 
 * @author Ángel Guzmán Maeso <shakaran@gmail.com>
 */
class CandidacyController extends Controller
{
	const CANDIDATE_UNASSIGNED = 0;
	const CANDIDATE_ACCEPTED   = 1;
	const CANDIDATE_REJECTED   = 2;
	const MIN_CANDIDACY_DAYS   = 2;
	const MIN_VOTE_CANDIDACY_DAYS = 2;
	
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
    	   
    			return $this->step1Action($request);
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
    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$conditions = $session->get('conditions');
    	
    	// Check conditions in step 1 for avoid bad usage
    	if(empty($conditions) || $conditions!= 'yes')
    	{
    		return $this->redirect($this->generateUrl('municipales_candidacy_step_conditions'), 301);
    	}
    	
    	$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    	$provinces_data = $province_repository->fetchProvinces();
    	
    	$municipalities = array();
    	$municipalities[0] = 'Elige municipio';
    	
    	if(isset($_REQUEST['candidacy_step1']) && isset($_REQUEST['candidacy_step1']['province']))
    	{
    		if(!empty($_REQUEST['candidacy_step1']['province']))
    		{
    			$province_form = intval($_REQUEST['candidacy_step1']['province']);
    			 
    			$query = "SELECT id, name FROM municipalities_spain WHERE province_id='" . intval($province_form) . "'";

    			$statement = $entity_manager->getConnection()->executeQuery($query);
    			$municipalities_data = $statement->fetchAll();
    			 
    			foreach($municipalities_data as $result)
    			{
    				$municipalities[$result['id']] = $result['name'];
    			}
    		}
    	}
    	
    	$translator = $this->get('translator');
    	$translations = array();
    	$translations['forms.candidacy_step1.password.minMessage'] = $translator->trans('forms.candidacy_step1.password.minMessage');
    	$translations['forms.candidacy_step1.password.maxMessage'] = $translator->trans('forms.candidacy_step1.password.maxMessage');
    	
    	$form = $this->createForm(new CandidacyStep1Type($provinces_data, $municipalities, $translations), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step1'),
			    'method' => 'POST',
    			)
    	);
    	
    	$form->handleRequest($request);

    	$ok = TRUE;
    	$already_registered = FALSE;
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

    		
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');

    		$admin_username = $admin_candidacy_repository->findOneBy(array('username' => $username));
    		 
    		if(!empty($admin_username))
    		{
    			$form->addError(new FormError('Ya existe un usuario administrador registrado con el nombre de usuario ' . $username));
    			$ok = FALSE;
    			$already_registered = TRUE;
    		}
    		
    		$admin_dni = $admin_candidacy_repository->findOneBy(array('dni' => $dni));
    			
    		if(!empty($admin_dni))
    		{
    			$form->addError(new FormError('Ya existe un usuario administrador registrado con el dni ' . $dni));
    			$ok = FALSE;
    			$already_registered = TRUE;
    		}
    		
    		$admin_email = $admin_candidacy_repository->findOneBy(array('email' => $email));
    		 
    		if(!empty($admin_email))
    		{
    			$form->addError(new FormError('Ya existe un usuario administrador registrado con el email ' . $email));
    			$ok = FALSE;
    			$already_registered = TRUE;
    		}
    		
    		$admin_phone = $admin_candidacy_repository->findOneBy(array('phone' => $phone));
    		 
    		if(empty($town) || $town == 0)
    		{
    			$form->addError(new FormError('El campo municipio es obligatorio'));
    			$ok = FALSE;
    		}
    		
    		$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    		$town_name = $province_repository->getMunicipalityName($town);
    		
    		if(empty($town_name))
    		{
    			$form->addError(new FormError('El campo municipio es obligatorio. No se ha encontrado un nombre de identificador valido para el ID de municipio ' . $town));
    			$ok = FALSE;
    		}
    		
    		if(!empty($admin_phone))
    		{
    			$form->addError(new FormError('Ya existe un usuario administrador registrado con el teléfono ' . $phone));
    			$ok = FALSE;
    			$already_registered = TRUE;
    		}
    		
    		if($already_registered)
    		{
    			$login_url = $this->generateUrl('login', array(), TRUE);
    			$form->addError(new FormError('Si te registraste con anterioridad, puedes acceder a tu registro en: <a href="' . $login_url . '" 
    					title="Login">' . $login_url . '</a> y continuar por el paso dónde lo dejaste.'));
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
    			$phone_verified->setMode(PhoneVerified::MODE_ADMIN);

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
	    		
	    		// Send mail with login link for admin
	    		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                $host= "listabierta.org";

                $message = \Swift_Message::newInstance()
	    		->setSubject('Tu cuenta de administrador ha sido creada')
	    		->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
	    		->setTo($email)
	    		->setBody(
	    				$this->renderView(
	    						'MunicipalesBundle:Mail:admin_created.html.twig',
	    						array('name' => $name)
	    				), 'text/html'
	    		);
	    		 
	    		$this->get('mailer')->send($message);
	    		
				return $this->stepVerifyAction($request);
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
    		$phone = $session->get('phone', NULL);
    		
    		if(empty($phone))
    		{
    			$form->addError(new FormError('El número de móvil no esta presente. ¿Sesión caducada?'));
    			$ok = FALSE;
    		}
    		
    		$email = $session->get('email', NULL);
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
    	$entity_manager = $this->getDoctrine()->getManager();
    	 
    	$admin_id = NULL;
    	
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
    	
    	if(!empty($admin_id))
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	
    		if(!empty($admin_candidacy))
    		{
    			$town = $admin_candidacy->getTown();
    		}
    	}
    	
    	if(empty($town))
    	{
    		$town = $session->get('town', NULL);
    	}
    	 
    	if(empty($town))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada para obtener la ciudad',
    		));
    	}
    	
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
    		
    		$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    		$town_name = $province_repository->getMunicipalityName($town);

    		$town_slug = $this->get('slugify')->slugify($town_name);
    		
    		$document_root = $this->getRequest()->server->get('DOCUMENT_ROOT'); // Must be 777
    		
    		$documents_path = 'docs/' . $town_slug . '/' . $admin_id;
    		
    		$fs = new Filesystem();
    			
    		if(!$fs->exists($document_root . '/' . $documents_path))
    		{
    			try
    			{
    				$fs->mkdir($document_root . '/' . $documents_path, 0700);
    			}
    			catch (IOExceptionInterface $e)
    			{
    				$form->addError(new FormError('An error occurred while creating your directory at: ' . $e->getPath()));
    				$ok = FALSE;
    			}
    		}
    		
    		// getMaxFilesize()
    		
    		if($program->isValid())
    		{
    			$program_data = $program->getData();
    		
    			if(!empty($program_data))
    			{
	    			if($program_data->getClientMimeType() !== 'application/pdf')
	    			{
	    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $program_data->getClientMimeType()));
	    				$ok = FALSE;
	    			}
	    		
	    			if($ok)
	    			{
	    				$program_data->move($document_root . '/' . $documents_path, 'program.pdf');
	    			}
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
    		
    			if(!empty($legal_conditions_data))
    			{
	    			if($legal_conditions_data->getClientMimeType() !== 'application/pdf')
	    			{
	    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $legal_conditions_data->getClientMimeType()));
	    				$ok = FALSE;
	    			}
	    		
	    			if($ok)
	    			{
	    				$legal_conditions_data->move($document_root . '/' . $documents_path, 'legal_conditions.pdf');
	    			}
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
    		
    			if(!empty($recall_term_data))
    			{
	    			if($recall_term_data->getClientMimeType() !== 'application/pdf')
	    			{
	    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $recall_term_data->getClientMimeType()));
	    				$ok = FALSE;
	    			}
	    		
	    			if($ok)
	    			{
	    				$recall_term_data->move($document_root . '/' . $documents_path, 'recall_term.pdf');
	    			}
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
    		
    			if(!empty($participatory_term_data))
    			{
	    			if($participatory_term_data->getClientMimeType() !== 'application/pdf')
	    			{
	    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $participatory_term_data->getClientMimeType()));
	    				$ok = FALSE;
	    			}
	    		
	    			if($ok)
	    			{
	    				$participatory_term_data->move($document_root . '/' . $documents_path, 'participatory_term.pdf');
	    			}
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
    		
    			if(!empty($voter_conditions_data))
    			{
	    			if($voter_conditions_data->getClientMimeType() !== 'application/pdf')
	    			{
	    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $voter_conditions_data->getClientMimeType()));
	    				$ok = FALSE;
	    			}
	    		
	    			if($ok)
	    			{
	    				$voter_conditions_data->move($document_root . '/' . $documents_path, 'voter_conditions.pdf');
	    			}
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
    		
    			if(!empty($technical_constrains_data))
    			{
	    			if($technical_constrains_data->getClientMimeType() !== 'application/pdf')
	    			{
	    				$form->addError(new FormError('MIMEType is not  application/pdf, found: ' . $technical_constrains_data->getClientMimeType()));
	    				$ok = FALSE;
	    			}
	    		
	    			if($ok)
	    			{
	    				$technical_constrains_data->move($document_root . '/' . $documents_path, 'technical_constrains.pdf');
	    			}
    			}
    		}
    		else
    		{
    			$form->addError(new FormError('technical constrainss pdf is not valid: ' . $technical_constrains_data->getErrorMessage()));
    			$ok = FALSE;
    		}
    		
    		if($ok)
    		{
    			if(!empty($program_data))
    			{
    				$session->set('program', $program_data->getClientOriginalName());
    			}
    			
    			if(!empty($legal_conditions_data))
    			{
    				$session->set('legal_conditions', $legal_conditions_data->getClientOriginalName());
    			}
    			
    			if(!empty($recall_term_data))
    			{
    				$session->set('recall_term', $recall_term_data->getClientOriginalName());
    			}

    			if(!empty($participatory_term_data))
    			{
    				$session->set('participatory_term', $participatory_term_data->getClientOriginalName());
    			}

    			if(!empty($voter_conditions_data))
    			{
    				$session->set('voter_conditions', $voter_conditions_data->getClientOriginalName());
    			}

    			if(!empty($technical_constrains_data))
    			{
    				$session->set('technical_constrains', $technical_constrains_data->getClientOriginalName());
    			}

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
    	
    	$admin_id = NULL;
    	
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
    	
    	if(!empty($admin_id))
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	
    		if(!empty($admin_candidacy))
    		{
    			$town = $admin_candidacy->getTown();
    			
    			$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    			$town_name = $province_repository->getMunicipalityName($town);
    		}
    	}

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
    		
    		if($total_days < self::MIN_CANDIDACY_DAYS)
    		{
    			$form->addError(new FormError('El número mínimo de dias entre la fecha inicial y final es ' . self::MIN_CANDIDACY_DAYS));
    			$ok = FALSE;
    		}

    		if($ok)
    		{
    			$session->set('from', $from_data->getTimestamp());
    			$session->set('to', $to_data->getTimestamp());

    			if(empty($admin_candidacy) || empty($admin_id))
    			{
    				return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    						'error' => 'No existe la candidatura de administrador para el identificador de administrador',
    				));
    			}
    			 
    			$admin_candidacy->setFromdate($from_data);
    			$admin_candidacy->setTodate($to_data);
    			 
    			$entity_manager->persist($admin_candidacy);
    			$entity_manager->flush();

    			return $this->step4Action($request);
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

    	$admin_id = NULL;

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
    	 
    	if(!empty($admin_id))
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		
    		if(!empty($admin_candidacy))
    		{
    			$town = $admin_candidacy->getTown();
    		}
    	}

    	if(empty($town))
    	{
	    	$town = $session->get('town', NULL);
    	}
    	
    	if(empty($town))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada para obtener la ciudad',
    		));
    	}
    	
    	$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    	$town_name = $province_repository->getMunicipalityName($town);

    	$default_address_slug = $this->get('slugify')->slugify($town_name);
    	
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

    		$address_slug = $this->get('slugify')->slugify($address_data);
    		
    		if(empty($address_slug))
    		{
    			$form->addError(new FormError('El slug de dirección no puede ser vacío'));
    			$ok = FALSE;
    		}
    		
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_address = $admin_candidacy_repository->findOneByAddress($address_slug);
    		
    		if(!empty($admin_address))
    		{
    			$form->addError(new FormError('La dirección ' . $admin_address . ' ya esta siendo utilizada por otra candidatura.'));
    			$ok = FALSE;
    		}
    		
    		if($ok)
    		{
    			$session->set('address', $address_slug);

    			$entity_manager = $this->getDoctrine()->getManager();

    			if(empty($admin_candidacy) || empty($admin_id))
    			{
    				throw $this->createNotFoundException('No existe la candidatura de administrador para guardar la dirección ' . $address_slug);
    			}
    			
    			$admin_candidacy->setAddress($address_slug);
    			
    			$entity_manager->persist($admin_candidacy);
    			$entity_manager->flush();

    			// Send email with register address to admin
    			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    			$message = \Swift_Message::newInstance()
	    			->setSubject('Enlace público de acceso para tu candidatura')
	    			->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
	    			->setTo($admin_candidacy->getEmail())
	    			->setBody(
	    					$this->renderView(
	    							'MunicipalesBundle:Mail:candidacy_address.html.twig',
	    							array('address_slug' => $address_slug, 
	    								  'name' => $admin_candidacy->getName())
	    					), 'text/html'
    			);
    			 
    			$this->get('mailer')->send($message);
    			
    			return $this->step5Action($request, $address_slug);
    		}
    	}
    	
    	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    	
    	return $this->render('MunicipalesBundle:Candidacy:step4.html.twig', array(
    			'form' => $form->createView(),
    			'default_address_slug' => $default_address_slug,
    			'address_slug' => $default_address_slug,
    			'host' => $host,
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
    	
    	$admin_id = NULL;
    	 
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
    	 
    	if(!empty($admin_id))
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		 
    		if(!empty($admin_candidacy))
    		{
    			$address_slug = $admin_candidacy->getAddress();
    		}
    	}
    	else 
    	{
    		$address_slug = $session->get('address', NULL);
    	}
    	
    	$form = $this->createForm(new CandidacyStep5Type(), NULL, array(
    			'action' => $this->generateUrl('municipales_candidacy_step5'),
    			'method' => 'POST',
    		)
    	);

    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		if($ok)
    		{
    			return $this->step6Action($request);
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
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    	$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	
    	if(empty($admin_candidacy))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
    	
    	$candidates = $candidate_repository->findBy(array('admin_id' => $admin_id));
    	
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

    	$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    	$town_name = $province_repository->getMunicipalityName($town);
    	
    	$town_slug = $this->get('slugify')->slugify($town_name);
    	
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
    	 
    	if(empty($admin_id))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	 
    	$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    	$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	 
    	if(empty($admin_candidacy))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$address = $admin_candidacy->getAddress();
    	
    	// Redirect to step 4 if no address set
    	if(empty($address))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha configurado una dirección de internet para la candidatura. Por favor <a href="' . $this->generateUrl('municipales_candidacy_step4') . '" title="Paso 4 Candidatura - Reserva una dirección de internet">establece una dirección en el paso 4 de la candidatura</a>',
    		));
    	}
    	
    	$candidacy_to_date = $session->get('to', NULL);
    	
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

    		if($total_days < self::MIN_VOTE_CANDIDACY_DAYS)
    		{
    			$form->addError(new FormError('El número mínimo de dias es ' . self::MIN_VOTE_CANDIDACY_DAYS));
    			$ok = FALSE;
    		}
    		
    		if($ok)
    		{
    			$session->set('total_days', $total_days);
    			
    			$admin_candidacy->setTotalDays($total_days);
    			 
    			$entity_manager->persist($admin_candidacy);
    			$entity_manager->flush();
    			
    			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    			$message = \Swift_Message::newInstance()
    			->setSubject('Enlace público de acceso de votaciones para tu candidatura')
    			->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
    			->setTo($admin_candidacy->getEmail())
    			->setBody(
    					$this->renderView(
    							'MunicipalesBundle:Mail:candidacy_vote_address.html.twig',
    							array('address_slug' => $address,
    									'name' => $admin_candidacy->getName())
    					), 'text/html'
    			);
    			
    			$this->get('mailer')->send($message);
    			
    			return $this->redirect($this->generateUrl('municipales_candidacy_step8'), 301);
    		}
    	}
    	
    	$town = $admin_candidacy->getTown();
    	$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    	$town_name = $province_repository->getMunicipalityName($town);
    	
    	return $this->render('MunicipalesBundle:Candidacy:step7.html.twig', array(
    			'form' => $form->createView(),
    			'candidacy_finished' => $candidacy_finished,
    			'address' => $address,
    			'town' => $town_name,
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
    	$session = $this->getRequest()->getSession();
    	
    	$admin_id = NULL;
    	 
    	$entity_manager = $this->getDoctrine()->getManager();
    	 
        $admin_id = NULL;

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
    	 
    	if(!empty($admin_id))
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		
    		if(!empty($admin_candidacy))
    		{
    			$town = $admin_candidacy->getTown();
    		}
    	}

    	if(empty($town))
    	{
	    	$town = $session->get('town', NULL);
    	}
    	
    	if(empty($town))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada para obtener la ciudad',
    		));
    	}
    	 
    	$address_slug = NULL;
    	
    	if(!empty($admin_id))
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		 
    		if(!empty($admin_candidacy))
    		{

    			$candidacy_to_date = $admin_candidacy->getTodate();
    			
    			if(empty($candidacy_to_date))
    			{
    				return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    						'error' => 'Error: no se ha configurado una fecha de candidatura final para la candidatura. Por favor <a href="' . $this->generateUrl('municipales_candidacy_step3') . '" title="Paso 3 Candidatura - Establece los plazos de presentación de candidaturas">establece los plazos de votación en el paso 3 de la candidatura</a>',
    				));
    			}
    			
    			$candidacy_total_days = $admin_candidacy->getTotalDays();
    			
    			if(empty($candidacy_total_days))
    			{
    				return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    						'error' => 'Error: no se ha configurado una fecha de plazo de votación para la candidatura. Por favor <a href="' . $this->generateUrl('municipales_candidacy_step7') . '" title="Paso 7 Admin Candidatura - Establece los plazos votación de candidaturas">establece los plazos de votación en el paso 7 de la candidatura</a>',
    				));
    			}
    		}
    		else 
    		{
    			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    					'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    			));
    		}
    	}
    	else
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$borda_points = $admin_candidacy->getBordaPoints();

    	// Use borda system defaults
    	if(empty($borda_points))
    	{
    		for($i = 0; $i <= 10; $i++)
    		{
    		// Apply borda system defaults values
    			$borda_points[$i] = $i != 0 ? 1 / $i : 0;
    		}
    		
    		$admin_candidacy->setBordaPoints($borda_points);
    		
    		$entity_manager->persist($admin_candidacy);
    		$entity_manager->flush();
    	}
    	
    	$now = new \Datetime('NOW');
    	
    	$candidaty_to_date_timestamp = $candidacy_to_date->getTimestamp();
    	$vote_end_date = $candidaty_to_date_timestamp + $candidacy_total_days * 24 * 3600;
    	
    	// Candidacy is finished, we can show the results
    	if($now->getTimestamp() - $vote_end_date > 0)
    	{
    		$candidacy_finished = TRUE;
    		
    		$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
    		
    		$voters = $voter_repository->findBy(array('admin_id' => $admin_id));
    		
    		$total_voters = 0;
    		$final_voters = array();
    		$results = array();
    		foreach($voters as $voter)
    		{
    			$vote_info = $voter->getVoteInfo();
    				
    			if(!empty($vote_info))
    			{
    				// Avoid count votes emited but not signed with Tractis
    				$vote_response_string = $voter->getVoteResponseString();
    				$vote_response_time   = $voter->getVoteResponseTime();
    				
    				if(!empty($vote_response_string) && !empty($vote_response_time))
    				{
	    				$total_voters += 1;
	    		
	    				$candidates = $vote_info['candidates'];
	    		
	    				foreach($candidates as $candidate)
	    				{
	    					$candidate_id = $candidate['id'];
	    					$candidate_points = $candidate['points'];
	    					if(isset($results[$candidate_id]))
	    					{
	    						$results[$candidate_id] += $borda_points[$candidate['points']];
	    					}
	    					else
	    					{
	    						$results[$candidate_id] = $borda_points[$candidate['points']];
	    					}
	    				}
    				}
    			}
    		}
    		
    		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
    		
    		$candidates_result = array();
    		if(!empty($results))
    		{
    			foreach($results as $result_id => $result_points)
    			{
    				$candidate_info = $candidate_repository->findOneById($result_id);
    		
    				if(!empty($candidate_info))
    				{
    					$candidate_aux = array();
    					$candidate_aux['id'] = $result_id;
    					$candidate_aux['name'] = $candidate_info->getName();
    					$candidate_aux['lastname'] = $candidate_info->getLastname();
    					$candidate_aux['dni'] = $candidate_info->getDNI();
    					$candidate_aux['phone'] = $candidate_info->getPhone();
    					$candidate_aux['points'] = $result_points;
    						
    					$candidates_result[] = $candidate_aux;
    				}
    			}
    		}
    		
    		$points = array();
    		foreach ($candidates_result as $key => $row)
    		{
    			$points[$key] = $row['points'];
    		}
    		
    		array_multisort($points, SORT_DESC, $candidates_result);
    		
    		$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    		$town_name = $province_repository->getMunicipalityName($town);
    		
    		if(empty($town_name))
    		{
    			$form->addError(new FormError('El campo municipio es obligatorio. No se ha encontrado un nombre de identificador valido para el ID de municipio ' . $town));
    			$ok = FALSE;
    		}
    		
    		$town_slug = $this->get('slugify')->slugify($town_name);
    			
    		$documents_path = 'docs/' . $town_slug . '/' . $admin_id . '/candidate/';
    	}
    	else // Candidacy is not finished. Hide the temporal results
    	{
    		$candidacy_finished = FALSE;
    		$town_name = NULL;
    		$documents_path = NULL;
    		$candidates = NULL;
    		$total_voters = NULL;
    		$candidates_result = array();
    	}
    	
    	return $this->render('MunicipalesBundle:Candidacy:step8.html.twig', array(
    			'candidacy_finished' => $candidacy_finished,
    			'vote_start_date' => $candidacy_to_date,
    			'vote_end_date' => $vote_end_date,
    			'town' => $town_name,
    			'total_voters' => $total_voters,
    			'documents_path' => $documents_path,
    			'candidates' => $candidates_result,
    			'borda_points' => $borda_points,
    			)
    		);
    }

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step9Action(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	 
    	$admin_id = NULL;
    	
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
    	
    	$address_slug = NULL;
    	if(!empty($admin_id))
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		 
    		if(!empty($admin_candidacy))
    		{
    			$address_slug = $admin_candidacy->getAddress();
    		}
    	}
    	else
    	{
    		$address_slug = $session->get('address', NULL);
    	}
    	
    	return $this->render('MunicipalesBundle:Candidacy:step9.html.twig', array('address_slug' => $address_slug));
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acceptAction($id = NULL, Request $request = NULL)
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
    	
    	if(empty($admin_id))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    	$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	
    	if(empty($admin_candidacy))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
    	
    	$candidate = $candidate_repository->findOneById($id);
    	
    	if(empty($candidate))
    	{
    		return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    				'error' => 'No existe el candidato para el identificador ' . $id,
    		));
    	}
    	
    	if(intval($admin_candidacy->getId()) !== intval($candidate->getAdminId()))
    	{
    		return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    				'error' => 'No coincide el identificador del administrador con el identificador candidato administrador numero ' . $id,
    		));
    	}
    	
    	$candidate->setStatus(self::CANDIDATE_ACCEPTED);
    	
    	$entity_manager->persist($candidate);
    	$entity_manager->flush();

    	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    	$message = \Swift_Message::newInstance()
    	->setSubject('Tu candidatura ha sido aceptada por el administrador')
    	->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
    	->setTo($candidate->getEmail())
    	->setBody(
    			$this->renderView(
    					'MunicipalesBundle:Mail:candidate_accepted.html.twig',
    					array(
    							'name' => $candidate->getName(), 
    							'admin_email' => $admin_candidacy->getEmail()
    					)
    			), 'text/html'
    	);
    	
    	return $this->redirect($this->generateUrl('municipales_candidacy_step6'), 301);
    }
      
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rejectAction($id = NULL, Request $request = NULL)
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
    	
    	if(empty($admin_id))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    	$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	
    	if(empty($admin_candidacy))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	
    	$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
    	 
    	$candidate = $candidate_repository->findOneById($id);
    	 
    	if(empty($candidate))
    	{
    		return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    				'error' => 'No existe el candidato para el identificador ' . $id,
    		));
    	}
    	 
    	if(intval($admin_candidacy->getId()) !== intval($candidate->getAdminId()))
    	{
    		return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    				'error' => 'No coincide el identificador del administrador con el identificador candidato administrador numero ' . $id,
    		));
    	}
    	 
    	$candidate->setStatus(self::CANDIDATE_REJECTED);
    	 
    	$entity_manager->persist($candidate);
    	$entity_manager->flush();  

    	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    	
    	$message = \Swift_Message::newInstance()
    	->setSubject('Tu candidatura ha sido rechazada por el administrador')
    	->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
    	->setTo($candidate->getEmail())
    	->setBody(
    			$this->renderView(
    					'MunicipalesBundle:Mail:candidate_rejected.html.twig',
    					array(
    							'name' => $candidate->getName(), 
    							'admin_email' => $admin_candidacy->getEmail()
    					)
    			), 'text/html'
    	);
    	
    	$this->get('mailer')->send($message);
    	
    	return $this->redirect($this->generateUrl('municipales_candidacy_step6'), 301);
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id = NULL, Request $request = NULL)
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
    	 
    	if(empty($admin_id))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	 
    	$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    	$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    	 
    	if(empty($admin_candidacy))
    	{
    		return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado la sesión de administrador iniciada. Accede desde el <a href="' . $this->generateUrl('login', array(), TRUE) . '" title="Login administrador">login</a>',
    		));
    	}
    	 
    	$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
    
    	$candidate = $candidate_repository->findOneById($id);
    
    	if(empty($candidate))
    	{
    		return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    				'error' => 'No existe el candidato para el identificador ' . $id,
    		));
    	}
    
    	if(intval($admin_candidacy->getId()) !== intval($candidate->getAdminId()))
    	{
    		return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    				'error' => 'No coincide el identificador del administrador con el identificador candidato administrador numero ' . $id,
    		));
    	}
    
    	$candidate_email = $candidate->getEmail();
    	$candidate_name  = $candidate->getName();
    
    	$entity_manager->remove($candidate);
    	$entity_manager->flush();
    
    	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    	 
    	$message = \Swift_Message::newInstance()
    	->setSubject('Tu candidatura ha sido borrada por el administrador')
    	->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
    	->setTo($candidate_email)
    	->setBody(
    			$this->renderView(
    					'MunicipalesBundle:Mail:candidate_deleted.html.twig',
    					array(
    						  'name' => $candidate_name, 
    						  'admin_email' => $admin_candidacy->getEmail()
    					)
    			), 'text/html'
    	);
    	 
    	$this->get('mailer')->send($message);
    	 
    	return $this->redirect($this->generateUrl('municipales_candidacy_step6'), 301);
    }
    
    public function changePasswdAction(Request $request)
    {
    	$changePasswordModel = new ChangePassword();
    	$form = $this->createForm(new ChangePasswordType(), $changePasswordModel, array(
    		'action' => $this->generateUrl('recover_password'),
    		'method' => 'POST',
    	));
    
    	$form->handleRequest($request);
    
    	if ($form->isSubmitted() && $form->isValid()) 
    	{
    		$old_pwd = $request->get('old_password');
    		$new_pwd = $request->get('new_password');
    		$user = $this->getUser();
    		$encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
    		$old_pwd_encoded = $encoder->encodePassword($old_pwd, $user->getSalt());
    		
    		if($user->getPassword() != $old_pwd_encoded) {
    			$session->getFlashBag()->set('error_msg', "Wrong old password!");
    		} else {
    			$new_pwd_encoded = $encoder->encodePassword($new_pwd, $user->getSalt());
    			$user->setPassword($new_pwd_encoded);
    			$manager = $this->getDoctrine()->getManager();
    			$manager->persist($user);
    		
    			$manager->flush();
    			$session->getFlashBag()->set('success_msg', "Password change successfully!");
    		}
    		return $this->render('@adminlte/profile/change_password.html.twig');
    		
    		// @todo encoding with MessageDigestPasswordEncoder and persist
    		return $this->redirect($this->generateUrl('change_passwd_success'));
    	}
    
    	return $this->render('MunicipalesBundle:Candidacy:changePasswd.html.twig', array(
    			'form' => $form->createView(),
    	));
    }

    public function recoverPasswordAction(Request $request)
    {
    	$entity_manager = $this->getDoctrine()->getManager();
    	$session = $this->getRequest()->getSession();
    	
    	$form = $this->createForm(new RecoverPasswordType(), NULL, array(
    			'action' => $this->generateUrl('recover_password'),
    			'method' => 'POST',
    	));
    	
    	$form->handleRequest($request);
    	
    	if ($form->isSubmitted() && $form->isValid())
    	{
    		$recover = $form['recover']->getData();
    		
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneByUsername($recover);
    		
    		$ok = TRUE;
    		if(empty($admin_candidacy))
    		{
    			$admin_candidacy = $admin_candidacy_repository->findOneByEmail($recover);
    			
    			if(empty($admin_candidacy))
    			{
    				$form->addError(new FormError('No se ha encontrado ningún nombre de usuario o correo para recuperar la contraseña'));
    				$ok = FALSE;
    			}
    		}
    		
    		if($ok)
    		{
    			$current_time = time();
    			$token = sha1($admin_candidacy->getId() + rand(0, 5000) + $current_time);
    			$name = $admin_candidacy->getName();
    			
    			$recovery_admin = new RecoveryAdmin();
    			$recovery_admin->setAdminId($admin_candidacy->getId());
    			$recovery_admin->setToken($token);
    			$recovery_admin->setTimestamp($current_time);
    			
    			$entity_manager->persist($recovery_admin);
    			$entity_manager->flush();
    			
    			// Send mail with login link for admin
    			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    			 
    			$message = \Swift_Message::newInstance()
    			->setSubject('Enlace de recuperacion de contraseña')
    			->setFrom('recovery@' . rtrim($host, '.'), 'Candidaturas')
    			->setTo($admin_candidacy->getEmail())
    			->setBody(
    					$this->renderView(
    							'MunicipalesBundle:Mail:recovery_password.html.twig',
    							array(
    									'name' => $name,
    									'token' => $token,
    							)
    					), 'text/html'
    			);
    			
    			$this->get('mailer')->send($message);
    			
    			$session->getFlashBag()->set('msg', "Se ha enviado un enlace de recuperación al correo en uso por la cuenta");
    			
    			return $this->render('MunicipalesBundle:Candidacy:recover_password_success.html.twig', array(
    			));
    		}
    		
    	}
    	
    	return $this->render('MunicipalesBundle:Candidacy:recover_password.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    public function recoveryTokenAction(Request $request, $token = NULL)
    {
    	$entity_manager = $this->getDoctrine()->getManager();
    	$session = $this->getRequest()->getSession();
    	
    	if(empty($token))
    	{
    		return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    				'error' => 'Error: El token no puede ser vacío',
    		));
    	}
    	else 
    	{
    		$recovery_admin_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\RecoveryAdmin');
    		$recovery_admin = $recovery_admin_repository->findOneByToken($token);
    		
    		if(empty($recovery_admin))
    		{
    			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    					'error' => 'Error: No existe ningún token que coincida para recuperar los datos',
    			));
    		}
    		else 
    		{
    			$now = time();
    			$timestamp = $recovery_admin->getTimestamp();
    			
    			if($now > $timestamp + 3600 * 24)
    			{
    				return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    						'error' => 'Error: El token de recuperación ha expirado. Sólo es válido durante 24 horas. Procede de nuevo a recuperar tu contraseña.',
    				));
    			}
    			else 
    			{
    				$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    				$admin_candidacy = $admin_candidacy_repository->findOneById($recovery_admin->getAdminId());
    				
    				if(empty($admin_candidacy))
    				{
    					return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
    							'error' => 'Error: El usuario para recuperar datos no existe.',
    					));
    				}
    				else 
    				{
    					$length = 10;
    					$chars = '234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    					$password = '';
    					$i = 0;
    					for($i = 0; $i < $length; $i++)
    					{
    						$password .= $chars[rand(0, strlen($chars) - 1)];
    					}
    					
    					$factory = $this->get('security.encoder_factory');
    					$encoder = $factory->getEncoder($admin_candidacy);
    					$encodedPassword = $encoder->encodePassword($password, $admin_candidacy->getSalt());
    					
    					$admin_candidacy->setPassword($encodedPassword);
    					
    					$entity_manager->persist($admin_candidacy);
    					$entity_manager->flush();
    					
    					// Send mail with login link for admin
    					$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    					 
    					$message = \Swift_Message::newInstance()
    					->setSubject('Nuevos datos de acceso')
    					->setFrom('recovery@' . rtrim($host, '.'), 'Candidaturas')
    					->setTo($admin_candidacy->getEmail())
    					->setBody(
    							$this->renderView(
    									'MunicipalesBundle:Mail:new_recover_data.html.twig',
    									array(
    											'admin' => $admin_candidacy,
    											'password' => $password,
    									)
    							), 'text/html'
    					);
    					
    					$this->get('mailer')->send($message);
    					
    					$recovery_admin->setTimestamp(0); //Invalidate token by time
    					$entity_manager->persist($recovery_admin);
    					$entity_manager->flush();
    					
    					$session->getFlashBag()->set('msg', "Se ha enviado un correo con los nuevos detalles de cuenta");
    					
    					return $this->render('MunicipalesBundle:Candidacy:recover_password_success.html.twig', array(
    					));
    				}
    			}
    		}
    	}
    }
}
