<?php
namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\Census\CensusStepConditionsType;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\Census\CensusStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\Census\CensusStepVerifyType;
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
 * CensusController
 * 
 * @author Ángel Guzmán Maeso <shakaran@gmail.com>
 */
class CensusController extends Controller
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
    	 
    	$form = $this->createForm(new CensusStepConditionsType(), NULL, array(
    			'action' => $this->generateUrl('census_step_conditions'),
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
    	   
    			return $this->step2LocationAction($request);
    		}
    	}
    	 
    	return $this->render('MunicipalesBundle:Census:step_conditions.html.twig', array(
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
    		return $this->redirect($this->generateUrl('census_step_conditions'), 301);
    	}
    	
    	$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    	$provinces_data = $province_repository->fetchProvinces();
    	
    	$municipalities = array();
    	$municipalities[0] = 'Elige municipio';
    	
    	if(isset($_REQUEST['census_step1']) && isset($_REQUEST['census_step1']['province']))
    	{
    		if(!empty($_REQUEST['census_step1']['province']))
    		{
    			$province_form = intval($_REQUEST['census_step1']['province']);
    			 
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
    	$translations['forms.census_step1.password.minMessage'] = $translator->trans('forms.census_step1.password.minMessage');
    	$translations['forms.census_step1.password.maxMessage'] = $translator->trans('forms.census_step1.password.maxMessage');
    	
    	$form = $this->createForm(new CensusStep1Type($provinces_data, $municipalities, $translations), NULL, array(
    			'action' => $this->generateUrl('census_step1'),
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
    	
    	return $this->render('MunicipalesBundle:Census:step3_register.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step4VerifyAction(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    
    	$form = $this->createForm(new CandidacyStepVerifyType(), NULL, array(
    			'action' => $this->generateUrl('census_step_verify'),
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
    			$this->step5FinishAction($request);
    		}
    	}
    
    	return $this->render('MunicipalesBundle:Census:step4_verify.html.twig', array(
    			'form' => $form->createView(),
    			'errors' => $form->getErrors(),
    	));
    }
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step2LocationAction(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	$entity_manager = $this->getDoctrine()->getManager();
    	 

    	$entity_manager = $this->getDoctrine()->getManager();
    	
    	$form = $this->createForm(new CandidacyStep2Type(), NULL, array(
    			'action' => $this->generateUrl('census_step2'),
    			'method' => 'POST',
    		)
    	);
    	 
    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$program              = $form['program'];
    		
    		

    		
    		if($ok)
    		{
    			if(!empty($program_data))
    			{
    				$session->set('program', $program_data->getClientOriginalName());
    			}
  
    			$warnings = array();
				
    			$form2 = $this->createForm(new CandidacyStep3Type(), NULL, array(
	    			'action' => $this->generateUrl('census_step3'),
	    			'method' => 'POST',
    			));
    	   
    			$form2->handleRequest($request);
    	   
    			return $this->render('MunicipalesBundle:Census:step3_register.html.twig', array(
    					'warnings' => $warnings,
    					'errors' => $form->getErrors(),
    					'form' => $form2->createView()
    				)
    			);
    		}
    	}
    	 
    	return $this->render('MunicipalesBundle:Census:step2_location.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step3RegisterAction(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    
    	$form = $this->createForm(new CandidacyStep3Type(), NULL, array(
    			'action' => $this->generateUrl('census_step3'),
    			'method' => 'POST',
    		)
    	);
    
    	$form->handleRequest($request);

    	$entity_manager = $this->getDoctrine()->getManager();

    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		$from_data    = $form['from']->getData();
 
    		if($ok)
    		{

    			return $this->step4VerifyAction($request);
    		}
    	}
    
    	return $this->render('MunicipalesBundle:Census:step3_register.html.twig', array(
    			'form' => $form->createView(),
    	));
    }
    
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function step5FinishAction(Request $request = NULL)
    {
    	return $this->render('MunicipalesBundle:Census:step5_finish.html.twig', array());
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
    
    	return $this->render('MunicipalesBundle:Census:changePasswd.html.twig', array(
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
    			
    			return $this->render('MunicipalesBundle:Census:recover_password_success.html.twig', array(
    			));
    		}
    		
    	}
    	
    	return $this->render('MunicipalesBundle:Census:recover_password.html.twig', array(
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
    					
    					return $this->render('MunicipalesBundle:Census:recover_password_success.html.twig', array(
    					));
    				}
    			}
    		}
    	}
    }
}
