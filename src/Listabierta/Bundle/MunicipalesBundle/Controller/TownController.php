<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Listabierta\Bundle\MunicipalesBundle\Entity\Voter;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep2Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep3Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep4Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep5Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep6Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep7Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStep8Type;

use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStepVerifyType;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\Vote\StepFilterType;

use Symfony\Component\Form\FormError;
use Symfony\Component\BrowserKit\Response;

require_once __DIR__ .  '/trusted_timestamps.php';
use TrustedTimestamps;

class TownController extends Controller
{
	const MAX_AVAILABLE_CANDIDATES = 5;
	
	public function indexAction(Request $request = NULL)
	{
		$this->step1Action($request);
	}
	
	private function verifyAdminAddress($address)
	{
		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
		
		if(empty($admin_candidacy) || $admin_candidacy == NULL || empty($address))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
				'error' => 'No existe la candidatura de administrador para cargar la dirección <b>' . $address . '</b>',
			));
		}
	}
	
	public function vote1Action($address = NULL, Request $request = NULL)
	{
		//@todo check end vote date
		
		$session = $this->getRequest()->getSession();
		$entity_manager = $this->getDoctrine()->getManager();
		
		$result = $this->verifyAdminAddress($address);

		if(!empty($result) && get_class($result) == 'Symfony\Component\HttpFoundation\Response')
		{
			return $result;
		}

		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
		
		$town = $admin_candidacy->getTown();
		$admin_id = $admin_candidacy->getId();
		
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
			
		$candidates = $candidate_repository->findBy(array('admin_id' => $admin_id));
		
		if(empty($candidates))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'No se puede iniciar la votación ya que aún no existen candidatos en esta candidatura',
			));
		}
		
		$form = $this->createForm(new TownStep1Type(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_vote_step1', array('address' => $address)),
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
			/*
			$username = $form['username']->getData();
			$password = $form['password']->getData();
			*/
			$email    = $form['email']->getData();
			$phone    = $form['phone']->getData();
			
			$entity_manager = $this->getDoctrine()->getManager();
			$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
			
			/*
			$voter_username = $voter_repository->findOneBy(array('username' => $username));
			 
			if(!empty($voter_username))
			{
				$form->addError(new FormError('Ya existe un usuario votante registrado con el nombre de usuario ' . $username));
				$ok = FALSE;
			}
			*/
			
			$voter_dni = $voter_repository->findOneBy(array('dni' => $dni));
			
			if(!empty($voter_dni))
			{
				$form->addError(new FormError('Ya existe un usuario votante registrado con el dni ' . $dni));
				$ok = FALSE;
			}
			
			$voter_email = $voter_repository->findOneBy(array('email' => $email));
			 
			if(!empty($voter_email))
			{
				$form->addError(new FormError('Ya existe un usuario votante registrado con el email ' . $email));
				$ok = FALSE;
			}
			
			$voter_phone = $voter_repository->findOneBy(array('phone' => $phone));
			 
			if(!empty($voter_phone))
			{
				$form->addError(new FormError('Ya existe un usuario registrado con el teléfono ' . $phone));
				$ok = FALSE;
			}
			
			if($ok)
			{
				$entity_manager = $this->getDoctrine()->getManager();
				 
				// Store info in database AdminCandidacy
				$voter = new Voter();
				$voter->setName($name);
				$voter->setLastname($lastname);
				$voter->setDni($dni);
				
				/*
				$voter->setUsername($username);
				 
				$factory = $this->get('security.encoder_factory');
				$encoder = $factory->getEncoder($voter);
				$encodedPassword = $encoder->encodePassword($password, $voter->getSalt());
			
				$voter->setPassword($encodedPassword);
				*/
				$voter->setEmail($email);
				$voter->setPhone($phone);
				 
				$entity_manager->persist($voter);
				$entity_manager->flush();
				 
				// Store email and phone in database as pending PhoneVerified without timestamp
				$phone_verified = new PhoneVerified();
				$phone_verified->setPhone($phone);
				$phone_verified->setEmail($email);
				$phone_verified->setTimestamp(0);
				$phone_verified->setMode(PhoneVerified::MODE_VOTER);
			
				$entity_manager->persist($phone_verified);
				$entity_manager->flush();
			
				$session->set('voter_id', $voter->getId());
				$session->set('voter_name', $name);
				$session->set('voter_lastname', $lastname);
				$session->set('voter_dni', $dni);
				$session->set('voter_phone', $phone);
				$session->set('voter_email', $email);

				$form2 = $this->createForm(new TownStepVerifyType(), NULL, array(
						'action' => $this->generateUrl('town_candidacy_step_verify', array('address' => $address)),
						'method' => 'POST',
				));
				 
				$form2->handleRequest($request);
				 
				return $this->render('MunicipalesBundle:Town:step_verify.html.twig', array(
						'form' => $form2->createView(),
						'address' => $address,
					)
				);
			}
		}

		return $this->render('MunicipalesBundle:Town:step1.html.twig', array(
				'town' => $town, 
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}

	/**
	 *
	 * @param Reqtrusted_timestamps.phpuest $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function voteVerifyAction(Request $request = NULL, $address = NULL)
	{
		$result = $this->verifyAdminAddress($address);

		if(!empty($result) && get_class($result) == 'Symfony\Component\HttpFoundation\Response')
		{
			return $result;
		}
		
		$session = $this->getRequest()->getSession();
	
		$entity_manager = $this->getDoctrine()->getManager();

		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
		
		$admin_id = $admin_candidacy->getId();
		
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
			
		$candidates = $candidate_repository->findBy(array('admin_id' => $admin_id));
		
		if(empty($candidates))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'No se puede iniciar la votación ya que aún no existen candidatos en esta candidatura',
			));
		}
		
		$form = $this->createForm(new TownStepVerifyType(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_step_verify', array('address' => $address)),
				'method' => 'POST',
		)
		);
		
		$form->handleRequest($request);
		
		$ok = TRUE;
		if ($form->isValid())
		{
			$phone = $session->get('voter_phone', NULL);
		
			if(empty($phone))
			{
				$form->addError(new FormError('El número de móvil no esta presente. ¿Sesión caducada?'));
				$ok = FALSE;
			}
		
			$email = $session->get('voter_email', NULL);
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
				$form2 = $this->createForm(new StepFilterType(), NULL, array(
						'action' => $this->generateUrl('town_candidacy_vote_step2', array('address' => $address)),
						'method' => 'POST',
				));
		
				$form2->handleRequest($request);
		
				return $this->render('MunicipalesBundle:Town:step_filter.html.twig', array(
						'form' => $form2->createView(),
						'address' => $address,
					)
				);
			}
		}
		
		return $this->render('MunicipalesBundle:Town:step_verify.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address,
		));
	}
	
		
	public function vote2Action($address = NULL, Request $request = NULL)
	{
		$result = $this->verifyAdminAddress($address);

		if(!empty($result) && get_class($result) == 'Symfony\Component\HttpFoundation\Response')
		{
			return $result;
		}
		
		$session = $this->getRequest()->getSession();
		
		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
		
		$admin_id = $admin_candidacy->getId();
		
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
			
		$candidates = $candidate_repository->findBy(array('admin_id' => $admin_id));
		
		if(empty($candidates))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'No se puede iniciar la votación ya que aún no existen candidatos en esta candidatura',
			));
		}
		
		$voter_id = $session->get('voter_id', NULL);
		
		if(empty($voter_id))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de votante para cargar la dirección ' . $address,
			));
		}
		
		$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
		
		$voter = $voter_repository->findOneById($voter_id);
		
		if(empty($voter))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'No existe el identificador de votante para cargar la dirección ' . $address_slug,
			));
		}
		
		$form = $this->createForm(new StepFilterType(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_vote_step2', array('address' => $address)),
				'method' => 'POST',
			)
		);
			
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$academic_level_option = $form['academic_level_option']->getData();
			$languages_option = $form['languages_option']->getData();
			$job_experience_option = $form['job_experience_option']->getData();
			$town_activities_option = $form['town_activities_option']->getData();
			$govern_priorities_option = $form['govern_priorities_option']->getData();
			$public_values_option = $form['public_values_option']->getData();
			
			if($academic_level_option == 'yes')
			{
				$academic_level = $form['academic_level']->getData();
			}
			else // Option no
			{
				$academic_level = 0;
			}
			
			if($languages_option == 'yes')
			{
				$languages      = $form['languages']->getData();
			}
			else // Option no
			{
				$languages = 0;
			}
			
			if($job_experience_option == 'yes')
			{
				$job_experience = $form['job_experience']->getData();
				
				if(count($job_experience) > 3)
				{
					$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
					$ok = FALSE;
				}
			}
			else // Option no
			{
				$job_experience = array();
			}

			if($town_activities_option == 'yes')
			{
				$town_activities = $form['town_activities']->getData();
				
				if(count($town_activities) > 3)
				{
					$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
					$ok = FALSE;
				}
			}
			else // Option no
			{
				$town_activities = array();
			}

			if($govern_priorities_option == 'yes')
			{
				$govern_priorities = $form['govern_priorities']->getData();
					
				if(count($govern_priorities) > 3)
				{
					$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
					$ok = FALSE;
				}
			}
			else // Option no
			{
				$govern_priorities = array();
			}
			
			if($public_values_option == 'yes')
			{
				$public_values = $form['public_values']->getData();
	
				if(count($public_values) > 3)
				{
					$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
					$ok = FALSE;
				}
			}
			else // Option no
			{
				$public_values = array();
			}
			
			if($ok)
			{			
				$session->set('voter_academic_level', $academic_level);
				$session->set('voter_languages', $languages);
			
				$session->set('voter_job_experience', $job_experience);
				$session->set('voter_town_activities', $town_activities);
				$session->set('voter_govern_priorities', $govern_priorities);
				$session->set('voter_public_values', $public_values);
				
				$voter->setAcademicLevel($academic_level);
				$voter->setLanguages($languages);
				$voter->setJobExperience($job_experience);
				$voter->setTownActivities($town_activities);
				$voter->setGovernPriorities($govern_priorities);
				$voter->setPublicValues($public_values);
					
				$entity_manager->persist($voter);
				$entity_manager->flush();
			
				return $this->vote7Action($address, $request);
				/*$form2 = $this->createForm(new TownStep7Type(), NULL, array(
						'action' => $this->generateUrl('town_candidacy_vote_step7', array('address' => $address)),
						'method' => 'POST',
				));
			
				$form2->handleRequest($request);
			
				return $this->render('MunicipalesBundle:Town:step7.html.twig', array(
						'address' => $address,
						'form' => $form2->createView()
					)
				);*/
			}
		}
	
		return $this->render('MunicipalesBundle:Town:step_filter.html.twig', array(
				'address' => $address,
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}

	public function vote7Action($address = NULL, Request $request = NULL)
	{
		$result = $this->verifyAdminAddress($address);

		if(!empty($result) && get_class($result) == 'Symfony\Component\HttpFoundation\Response')
		{
			return $result;
		}
	
		$session = $this->getRequest()->getSession();
		$entity_manager = $this->getDoctrine()->getManager();
	
		$voter_id = $session->get('voter_id', NULL);
	
		if(empty($voter_id))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de votante para cargar la dirección ' . $address,
			));
		}
	
		$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
	
		$voter = $voter_repository->findOneById($voter_id);
	
		if(empty($voter))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'No existe el identificador de votante para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new TownStep7Type(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_vote_step7', array('address' => $address)),
				'method' => 'POST',
			)
		);
			
		$form->handleRequest($request);
	
		$ok = TRUE;
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
		
		$admin_id = $admin_candidacy->getId();
		
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
		 
		$candidates = $candidate_repository->findAll(array('admin_id' => $admin_id));
		
		if(empty($candidates))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'No existen candidatos en esta candidatura',
			));
		}
		
		// Filter only candidates accepted
		$valid_candidates = array();
		foreach($candidates as $candidate)
		{
			if($candidate->getStatus() == 1)
			{
				$valid_candidates[] = $candidate;
			}
		}
		
		// @todo Filter candidates with voter filters
		$academic_level = $voter->getAcademicLevel();
		$languages = $voter->getLanguages();
		$job_experience = $voter->getJobExperience();
		$town_activities = $voter->getTownActivities();
		$govern_priorities = $voter->getGovernPriorities();
		$public_values = $voter->getPublicValues();
		
		// Filter by academic level voter option
		$result_candidates = array();
		if(!empty($academic_level) && $academic_level > 0)
		{
			foreach($valid_candidates as $candidate)
			{
				if($candidate->getAcademicLevel() == $academic_level)
				{
					$result_candidates[] = $candidate;
				}
			}
		}
		else // No filter applied
		{
			$result_candidates = $valid_candidates;
		}
		
		$valid_candidates = $result_candidates;
		
		// Filter by language voter option
		$result_candidates = array();
		if(!empty($languages) && count($languages) > 0)
		{
			foreach($valid_candidates as $candidate)
			{
				$found = FALSE;
				foreach($languages as $language)
				{
					if($found == FALSE && in_array($language, $candidate->getLanguages()))
					{
						$result_candidates[] = $candidate;
						$found = TRUE;
					}
				}
			}
		}
		else // No filter applied
		{
			$result_candidates = $valid_candidates;
		}
		
		$valid_candidates = $result_candidates;
		
		// Filter by job experience voter option
		$result_candidates = array();
		if(!empty($job_experience) && count($job_experience) > 0)
		{
			foreach($valid_candidates as $candidate)
			{
				$found = FALSE;
				foreach($job_experience as $job)
				{
					if($found == FALSE && in_array($job, $candidate->getJobExperience()))
					{
						$result_candidates[] = $candidate;
						$found = TRUE;
					}
				}
			}
		}
		else // No filter applied
		{
			$result_candidates = $valid_candidates;
		}
		
		$valid_candidates = $result_candidates;

		// Filter by town activities voter option
		$result_candidates = array();
		if(!empty($town_activities) && count($town_activities) > 0)
		{
			foreach($valid_candidates as $candidate)
			{
				$found = FALSE;
				foreach($town_activities as $town_activity)
				{
					if($found == FALSE && in_array($town_activity, $candidate->getTownActivities()))
					{
						$result_candidates[] = $candidate;
						$found = TRUE;
					}
				}
			}
		}
		else // No filter applied
		{
			$result_candidates = $valid_candidates;
		}
		
		$valid_candidates = $result_candidates;
		
		// Filter by govern priorities voter option
		$result_candidates = array();
		if(!empty($govern_priorities) && count($govern_priorities) > 0)
		{
			foreach($valid_candidates as $candidate)
			{
				$found = FALSE;
				foreach($govern_priorities as $govern_priority)
				{
					if($found == FALSE && in_array($govern_priority, $candidate->getGovernPriorities()))
					{
						$result_candidates[] = $candidate;
						$found = TRUE;
					}
				}
			}
		}
		else // No filter applied
		{
			$result_candidates = $valid_candidates;
		}
		
		$valid_candidates = $result_candidates;
	
		// Filter by public values voter option
		$result_candidates = array();
		if(!empty($public_values) && count($public_values) > 0)
		{
			foreach($valid_candidates as $candidate)
			{
				$found = FALSE;
				foreach($public_values as $public_value)
				{
					if($found == FALSE && in_array($public_value, $candidate->getPublicValues()))
					{
						$result_candidates[] = $candidate;
						$found = TRUE;
					}
				}
			}
		}
		else // No filter applied
		{
			$result_candidates = $valid_candidates;
		}
		
		$valid_candidates = $result_candidates;
		
		// Filter candidates with voter levels here until MAX_AVAILABLE_CANDIDATES
		//$valid_candidates = array_slice($valid_candidates, 0, self::MAX_AVAILABLE_CANDIDATES);
		
		// Random position
		shuffle($valid_candidates);
		
		$town = $admin_candidacy->getTown();
		
		$town_slug = $this->get('slugify')->slugify($town);
		 
		$documents_path = 'docs/' . $town_slug . '/' . $admin_id . '/candidate/';

		if ($form->isValid())
		{
			$extra_data = $form->getExtraData();
			
			$vote_info = array();
			$vote_info['admin_id'] = $admin_id;
			$vote_info['voter_id'] = $voter_id;
			
			$candidate_voters = array();
			$candidate_points_values = array();
			foreach($extra_data as $candidate_key => $candidate_points)
			{
				$candidate_id = intval(str_replace('candidate_', '', $candidate_key));
					
				$candidate_voters[] = array('id' => $candidate_id, 'points' => intval($candidate_points));
				$candidate_points_values[] = $candidate_points;
			}
			
			if(count($extra_data) != count(array_unique($candidate_points_values)))
			{
				$form->addError(new FormError('Las puntuaciones asignadas no pueden repetirse'));
				$ok = FALSE;
			}
			
			$vote_info['candidates'] = $candidate_voters;
			
			//var_dump($vote_info);
			
			if($ok)
			{
				// Tractis TSA here

				// Create an API Key here: https://www.tractis.com/webservices/tsa/apikeys
				$tractis_api_identifier = $this->container->getParameter('tractis_api_identifier');
				$tractis_api_secret = $this->container->getParameter('tractis_api_secret');
				
				$current_time = time();
				
				$tsa_cert_chain_file = '/tmp/chain-' . $admin_id . '-' . $voter_id . '-' . $current_time . '.txt';
				
				$myfile = @fopen($tsa_cert_chain_file, "w");
				
				$my_hash = sha1(serialize($vote_info));
				
				$requestfile_path = \TrustedTimestamps::createRequestfile($my_hash);
				$response = \TrustedTimestamps::signRequestfile($requestfile_path, "https://api.tractis.com/rfc3161tsa", $tractis_api_identifier, $tractis_api_secret);
				print_r($response);
				
				/*
				 Array
				 (
				 [response_string] => Shitload of text (base64-encoded Timestamp-Response of the TSA)
				 [response_time] => 1299098823
				 )
				 */
				
				if(empty($response))
				{
					return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
							'error' => 'Error en el firmado de voto TSA. Respuesta vacía',
					));
				}
				
				if(empty($response['response_string']))
				{
					return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
							'error' => 'Error en el firmado de voto TSA. Respuesta con cadena vacía',
					));
				}
				
				if(empty($response['response_time']))
				{
					return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
							'error' => 'Error en el firmado de voto TSA. Respuesta con tiempo vacía',
					));
				}
				
				//echo \TrustedTimestamps::getTimestampFromAnswer($response['response_string']); //1299098823
				try 
				{
					$validate = \TrustedTimestamps::validate($my_hash, $response['response_string'], $response['response_time'], $tsa_cert_chain_file);
					//var_dump($validate);
				} 
				catch (Exception $e) 
				{
					$logger = $this->get('logger')->error('Vote validation Error: ' . $e->getMessage());
				}
				
				/*
				 
				$validate = \TrustedTimestamps::validate($my_hash, $response['response_string'], $response['response_time'], $tsa_cert_chain_file);
				print_r("\nValidation result\n");
				var_dump($validate); //bool(true)
				
				//now with an incorrect hash. Same goes for a manipulated response string or response time
				$validate = \TrustedTimestamps::validate(sha1("im not the right hash"), 
								$response['response_string'], 
									$response['response_time'], 
						$tsa_cert_chain_file);
				print_r("\nValidation result after content manipulation\n");
				var_dump($validate); //bool(false)
				*/
				
				
				$form2 = $this->createForm(new TownStep8Type(), NULL, array(
						'action' => $this->generateUrl('town_candidacy_vote_step8', array('address' => $address)),
						'method' => 'POST',
				));
					
				$form2->handleRequest($request);
	
					
				return $this->render('MunicipalesBundle:Town:step8.html.twig', array(
						'address' => $address,
						'form' => $form2->createView()
					)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Town:step7.html.twig', array(
				'address' => $address,
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'candidates' => $valid_candidates,
				'documents_path' => $documents_path,
		));
	}
	
	public function vote8Action($address = NULL, Request $request = NULL)
	{
		$result = $this->verifyAdminAddress($address);

		if(!empty($result) && get_class($result) == 'Symfony\Component\HttpFoundation\Response')
		{
			return $result;
		}
	
		$session = $this->getRequest()->getSession();
		$entity_manager = $this->getDoctrine()->getManager();
	
		$voter_id = $session->get('voter_id', NULL);
	
		if(empty($voter_id))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de votante para cargar la dirección ' . $address,
			));
		}
	
		$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
	
		$voter = $voter_repository->findOneById($voter_id);
	
		if(empty($voter))
		{
			return $this->render('MunicipalesBundle:Town:step1_unknown.html.twig', array(
					'error' => 'No existe el identificador de votante para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new TownStep8Type(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_vote_step8', array('address' => $address)),
				'method' => 'POST',
		)
		);
			
		$form->handleRequest($request);
	
		$ok = TRUE;
	
		if ($form->isValid())
		{
			if($ok)
			{
				return $this->render('MunicipalesBundle:Town:step_results.html.twig', array(
						'address' => $address,
					)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Town:step8.html.twig', array(
				'address' => $address,
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}
	
	public function resultsAction($town = NULL, Request $request = NULL)
	{
		return $this->render('MunicipalesBundle:Town:step_results.html.twig', array(
				'town' => $town,
		));
	}	
}