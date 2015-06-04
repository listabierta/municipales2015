<?php
namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\Census\CensusStepConditionsType;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\Census\CensusStep2Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\Census\CensusStep3Type;
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
    			'action' => $this->generateUrl('census_step1'),
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
    public function step2LocationAction(Request $request = NULL)
    {
    	$session = $this->getRequest()->getSession();
    	$entity_manager = $this->getDoctrine()->getManager();
    
    	$conditions = $session->get('conditions');
    
    	// Check conditions in step 1 for avoid bad usage
    	if(empty($conditions) || $conditions!= 'yes')
    	{
    		return $this->redirect($this->generateUrl('census_step1'), 301);
    	}
    
    	$form = $this->createForm(new CensusStep2Type(), NULL, array(
    			'action' => $this->generateUrl('census_step2'),
    			'method' => 'POST',
    	)
    	);
    
    	$form->handleRequest($request);
    
    	$ok = TRUE;
    	if ($form->isValid())
    	{
    		if($ok)
    		{
    			return $this->step3RegisterAction($request);
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
    	$entity_manager = $this->getDoctrine()->getManager();

    	$form = $this->createForm(new CensusStep3Type(), NULL, array(
    			'action' => $this->generateUrl('census_step3'),
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
    		$email    = $form['email']->getData();
    		$phone    = $form['phone']->getData();

    		
    		$census_user_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\CensusUser');
    		
    		$census_user_dni = $census_user_repository->findOneBy(array('dni' => $dni));
    			
    		if(!empty($census_user_dni))
    		{
    			$form->addError(new FormError('Ya existe un usuario de censo registrado con el dni ' . $dni));
    			$ok = FALSE;
    			$already_registered = TRUE;
    		}
    		
    		$census_user_email = $census_user_repository->findOneBy(array('email' => $email));
    		 
    		if(!empty($census_user_email))
    		{
    			$form->addError(new FormError('Ya existe un usuario de censo registrado con el email ' . $email));
    			$ok = FALSE;
    			$already_registered = TRUE;
    		}
    		
    		$census_user_phone = $census_user_repository->findOneBy(array('phone' => $phone));

    		if(!empty($census_user_phone))
    		{
    			$form->addError(new FormError('Ya existe un usuario de censo registrado con el teléfono ' . $phone));
    			$ok = FALSE;
    			$already_registered = TRUE;
    		}
    		
    		/*
    		if($already_registered)
    		{
    			$login_url = $this->generateUrl('login', array(), TRUE);
    			$form->addError(new FormError('Si te registraste con anterioridad, puedes acceder a tu registro en: <a href="' . $login_url . '" 
    					title="Login">' . $login_url . '</a> y continuar por el paso dónde lo dejaste.'));
    		}
    		*/
    		
    		if($ok)
    		{
    			$session->set('geolocation_allowed', TRUE);

    			$entity_manager = $this->getDoctrine()->getManager();
    			
    			// Store info in database AdminCandidacy
    			$census_user = new CensusUser();
    			$census_user->setName($name);
    			$census_user->setLastname($lastname);
    			$census_user->setDni($dni);
    			$census_user->setEmail($email);
    			$census_user->setPhone($phone);
    			
    			$entity_manager->persist($census_user);
    			$entity_manager->flush();
    			
    			// Store email and phone in database as pending PhoneVerified without timestamp
    			$phone_verified = new PhoneVerified();
    			$phone_verified->setPhone($phone);
    			$phone_verified->setEmail($email);
    			$phone_verified->setTimestamp(0);
    			$phone_verified->setMode(PhoneVerified::MODE_CENSUS_USER);

    			$entity_manager->persist($phone_verified);
    			$entity_manager->flush();

    			$session->set('census_user_id', $admin_candidacy->getId());
	    		$session->set('name', $name);
	    		$session->set('lastname', $lastname);
	    		$session->set('dni', $dni);
	    		$session->set('email', $email);
	    		$session->set('phone', $phone);
	    		
				return $this->step4VerifyAction($request);
    		}
    	}
    	
    	return $this->render('MunicipalesBundle:Census:step3_register.html.twig', array(
    			'form' => $form->createView(),
    			'enable_geolocation' => TRUE,
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
    
    	$form = $this->createForm(new CensusStepVerifyType(), NULL, array(
    			'action' => $this->generateUrl('census_step4'),
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
    			return $this->step5FinishAction($request);
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
