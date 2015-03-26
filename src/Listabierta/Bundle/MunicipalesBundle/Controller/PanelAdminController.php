<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidacyStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\ModifyVotePointsSystemType;

class PanelAdminController extends Controller
{
    public function indexAction(Request $request)
    {
    	$session = $this->getRequest()->getSession();
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
    	
    	$needs_phone_verification = FALSE;
    	
    	// Redirect to login
    	if(empty($admin_id))
    	{
    		return $this->redirect($this->generateUrl('login'));
    	}
    	else 
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		 
    		if(empty($admin_candidacy))
    		{
    			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    				'error' => 'Error: no se ha encontrado el identificador de administrador ' . $admin_id . ' en la base de datos',
    			));
    		}
    		else
    		{
    			$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
    			 
    			$phone = $admin_candidacy->getPhone();
    			$email = $admin_candidacy->getEmail();
    			
    			$phone_status = $phone_verified_repository->findOneBy(array('phone' => $phone, 'email' => $email));
    			 
    			if(empty($phone_status) || $phone_status->getTimestamp() == 0)
    			{
    				$needs_phone_verification = TRUE;
    			}
    			
    			$town = $admin_candidacy->getTown();
    			
    			// @todo
    			
    		}
    	}
    	
        return $this->render('MunicipalesBundle:PanelAdmin:index.html.twig', array(
        		'admin' => $admin_candidacy,
        		'needs_phone_verification' => $needs_phone_verification,
        ));
    }
    
    public function modifyPersonalDataAction(Request $request)
    {
    	$session = $this->getRequest()->getSession();
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
    	 
    	// Redirect to login
    	if(empty($admin_id))
    	{
    		return $this->redirect($this->generateUrl('login'));
    	}
    	else
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		 
    		if(empty($admin_candidacy))
    		{
    			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    					'error' => 'Error: no se ha encontrado el identificador de administrador ' . $admin_id . ' en la base de datos',
    			));
    		}
    		else
    		{
    			$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
    			$provinces_data = $province_repository->fetchProvinces();
    			 
    			$municipalities = array();
    			$municipalities[0] = 'Elige municipio';
    			
    			if(isset($_REQUEST['candidacy_step1']) && isset($_REQUEST['candidacy_step1']['province']))
    			{
    				if(!empty($_REQUEST['candidacy_step1']['province']))
    				{
    					$province_form = intval($_REQUEST['candidacy_step1']['province']);
    					 
    					$municipalities = array();
    					$municipalities[0] = 'Elige municipio';
    			
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
    					'action' => $this->generateUrl('panel_admin_modify_personal_data'),
    					'method' => 'POST',
    				)
    			);
    			
    			if (!$request->isMethod('POST'))
    			{
    				$form->get('name')->setData($admin_candidacy->getName());
    				$form->get('lastname')->setData($admin_candidacy->getLastname());
    				$form->get('dni')->setData($admin_candidacy->getDni());
    				$form->get('username')->setData($admin_candidacy->getUsername());
    				$form->get('password')->setData($admin_candidacy->getPassword());
    				$form->get('email')->setData($admin_candidacy->getEmail());
    				$form->get('province')->setData($admin_candidacy->getProvince());
    				$form->get('town')->setData($admin_candidacy->getTown());
    				$form->get('phone')->setData($admin_candidacy->getPhone());
    			}
    			
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
    			
    				/*
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
    				*/
    				
    				
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
    			
    				$admin_phone = $admin_candidacy_repository->findOneBy(array('phone' => $admin_candidacy->getPhone()));
    				if(empty($admin_phone))
    				{
    					$form->addError(new FormError('No existe un usuario administrador registrado con el teléfono ' . $phone . ' para cambiar sus datos'));
    					$ok = FALSE;
    				}
    				elseif($admin_phone->getId() != $admin_id)
    				{
    					$form->addError(new FormError('El número de teléfono en uso ' . $phone . ' no se corresponde para este administrador ' . $admin_id . ' para cambiar sus datos'));
    					$ok = FALSE;
    				}
    				
    				$admin_phone = $admin_candidacy_repository->findOneBy(array('phone' => $phone));
    				if(!empty($admin_phone) && $admin_phone->getId() != $admin_id)
    				{
    					$form->addError(new FormError('Otro usuario ya tiene en uso el teléfono ' . $phone));
    					$ok = FALSE;
    				}
    			
    				if($already_registered)
    				{
    					$form->addError(new FormError('Si te registraste con anterioridad, puedes acceder a tu registro en: <a href="http://municipales2015.listabierta.org/login" title="Login">http://municipales2015.listabierta.org/login</a> y continuar por el paso dónde lo dejaste.'));
    				}
    			
    				if($ok)
    				{
    					// Store info in database AdminCandidacy
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
    					
    					$old_admin_phone = $admin_candidacy->getPhone();
    					if($old_admin_phone != $phone)
    					{
    						$admin_candidacy->setPhone($phone);
    					}
    					 
    					$entity_manager->persist($admin_candidacy);
    					$entity_manager->flush();
    					 
    					if($old_admin_phone != $phone)
    					{
	    					// Store email and phone in database as pending PhoneVerified without timestamp
	    					$phone_verified = new PhoneVerified();
	    					$phone_verified->setPhone($phone);
	    					$phone_verified->setEmail($email);
	    					$phone_verified->setTimestamp(0);
	    					$phone_verified->setMode(PhoneVerified::MODE_ADMIN);
	    			
	    					$entity_manager->persist($phone_verified);
	    					$entity_manager->flush();
    					}
    			
    					$session->set('admin_id', $admin_candidacy->getId());
    					$session->set('name', $name);
    					$session->set('lastname', $lastname);
    					$session->set('dni', $dni);
    					$session->set('email', $email);
    					$session->set('province', $province);
    					$session->set('town', $town);
    					$session->set('phone', $phone);
    					 
    					// Send mail with login link for admin
    					 
    					$message = \Swift_Message::newInstance()
    					->setSubject('Tu cuenta de administrador ha sido actualizada')
    					->setFrom('candidaturas@municipales2015.listabierta.org', 'Candidaturas')
    					->setTo($email)
    					->setBody(
    							$this->renderView(
    									'MunicipalesBundle:Mail:admin_updated.html.twig',
    									array('name' => $name)
    							), 'text/html'
    					);
    			
    					$this->get('mailer')->send($message);
    					 
    					$this->get('session')->getFlashBag()->set('msg', 'Datos personales actualizados correctamente');

    					return $this->redirectToRoute('panel_admin');
    				}
    			}
    			 
    			return $this->render('MunicipalesBundle:PanelAdmin:modify_personal_data.html.twig', array(
    					'form' => $form->createView(),
    					'admin' => $admin_candidacy,
    			));
    		}
    	}
    }
    
    public function modifyVotePointsSystemAction(Request $request)
    {
    	$session = $this->getRequest()->getSession();
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
    
    	// Redirect to login
    	if(empty($admin_id))
    	{
    		return $this->redirect($this->generateUrl('login'));
    	}
    	else
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		 
    		if(empty($admin_candidacy))
    		{
    			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    					'error' => 'Error: no se ha encontrado el identificador de administrador ' . $admin_id . ' en la base de datos',
    			));
    		}
    		else
    		{
    			$form = $this->createForm(new ModifyVotePointsSystemType(), NULL, array(
    					'action' => $this->generateUrl('panel_admin_modify_vote_points_system'),
    					'method' => 'POST',
    				)
    			);
    			
    			$borda_points = $admin_candidacy->getBordaPoints();
    			
    			if(empty($borda_points))
    			{
    				for($i = 0; $i <= 10; $i++)
    				{
    					// Apply borda system defaults values
    					$form['vote' . $i]->setData($i != 0 ? 1 / $i : 0);
    				}
    			}
    			else 
    			{
    				for($i = 0; $i <= 10; $i++)
    				{
    					// Load borda system values from database
    					$form['vote' . $i]->setData($borda_points[$i]);
    				}
    			}
    			
    			$form->handleRequest($request);

    			if ($form->isValid())
    			{
    				$borda_points = array();
    				
    				for($i = 0; $i <= 10; $i++)
    				{
    					$vote{$i} = $form['vote' . $i]->getData();
    					
    					$borda_points[$i] = $vote{$i};
    				}
    				
    				$admin_candidacy->setBordaPoints($borda_points);
    				
    				$entity_manager->persist($admin_candidacy);
    				$entity_manager->flush();
    				
    				$this->get('session')->getFlashBag()->set('msg', 'Puntuación de la votación actualizada correctamente');
    				
    				return $this->redirectToRoute('panel_admin');
    			}
    			
    			return $this->render('MunicipalesBundle:PanelAdmin:modify_vote_points_system.html.twig', array(
    					'form' => $form->createView(),
    					'admin' => $admin_candidacy,
    			));
    		}
    	}
    }
    
    public function modifyVotePointsSystemResetAction(Request $request)
    {
    	$session = $this->getRequest()->getSession();
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
    
    	// Redirect to login
    	if(empty($admin_id))
    	{
    		return $this->redirect($this->generateUrl('login'));
    	}
    	else
    	{
    		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
    		$admin_candidacy = $admin_candidacy_repository->findOneById($admin_id);
    		 
    		if(empty($admin_candidacy))
    		{
    			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
    					'error' => 'Error: no se ha encontrado el identificador de administrador ' . $admin_id . ' en la base de datos',
    			));
    		}
    		else
    		{
    			for($i = 0; $i <= 10; $i++)
    			{
    				// Apply borda system defaults values
    				$borda_points[$i] = $i != 0 ? 1 / $i : 0;
    			}
    				
    			$admin_candidacy->setBordaPoints($borda_points);
    
    			$entity_manager->persist($admin_candidacy);
    			$entity_manager->flush();
    
    			$this->get('session')->getFlashBag()->set('msg', 'Puntuación de la votación reiniciada a valores por defecto correctamente');
    
    			return $this->redirectToRoute('panel_admin');
    		}
    	}
    }
    
}
