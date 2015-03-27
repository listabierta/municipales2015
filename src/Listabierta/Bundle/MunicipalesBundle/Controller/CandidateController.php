<?php

namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep1Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStepVerifyType;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep2Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep3Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep4Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep5Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep6Type;
use Listabierta\Bundle\MunicipalesBundle\Form\Type\CandidateStep7Type;

use Listabierta\Bundle\MunicipalesBundle\Entity\Candidate;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


use Symfony\Component\Form\FormError;

class CandidateController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$this->step1Action($request);
	}
	
	public function step1Action($address = NULL, Request $request = NULL)
	{
		$session = $this->getRequest()->getSession();
		
		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');

		$address_slug = $this->get('slugify')->slugify($address);
		
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
		
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));		
		}
		
		$candidacy_from = $admin_candidacy->getFromdate();
		$candidacy_to = $admin_candidacy->getTodate();
		
		if(empty($candidacy_from))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe fecha de candidatura de inicio para ' . $address_slug,
			));
		}
		
		if(empty($candidacy_to))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe fecha de candidatura de inicio para ' . $address_slug,
			));
		}
		
		$now = new \Datetime('NOW');
		//$now->add(\DateInterval::createFromDateString('+7 days')); // Debugging

		if($now->getTimestamp() - $candidacy_from->getTimestamp() < 0)
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'La candidatura aún no esta abierta para ' . $address_slug . ', la fecha de apertura es ' . $candidacy_from->format('d-m-Y'),
			));
		}
		
		if($now->getTimestamp() - $candidacy_to->getTimestamp() > 0)
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'La candidatura esta cerrada para ' . $address_slug . ', la fecha de finalización fue ' . $candidacy_to->format('d-m-Y'),
			));
		}
		
		$candidacy_total_days = $admin_candidacy->getTotalDays();
		 
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}

		$form = $this->createForm(new CandidateStep1Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step1', array('address' => $address)),
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
			$phone    = $form['phone']->getData();
			
			$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
			
			$candidate_dni = $candidate_repository->findOneBy(array('dni' => $dni));
			 
			if(!empty($candidate_dni))
			{
				$form->addError(new FormError('Ya existe un usuario candidato registrado con el dni ' . $dni));
				$ok = FALSE;
			}
			
			$candidate_email = $candidate_repository->findOneBy(array('email' => $email));
			 
			if(!empty($candidate_email))
			{
				$form->addError(new FormError('Ya existe un usuario candidato registrado con el email ' . $email));
				$ok = FALSE;
			}
			
			$candidate_phone = $candidate_repository->findOneBy(array('phone' => $phone));
			 
			if(!empty($candidate_phone))
			{
				$form->addError(new FormError('Ya existe un usuario candidato registrado con el teléfono ' . $phone));
				$ok = FALSE;
			}

			if($ok)
			{
				// Store info in database AdminCandidacy
				$candidate = new Candidate();
				$candidate->setName($name);
				$candidate->setLastname($lastname);
				$candidate->setDni($dni);
				$candidate->setEmail($email);
				$candidate->setPhone($phone);
				$candidate->setAdminId($admin_candidacy->getId());
				 
				$entity_manager->persist($candidate);
				$entity_manager->flush();
				 
				// Store email and phone in database as pending PhoneVerified without timestamp
				$phone_verified = new PhoneVerified();
				$phone_verified->setPhone($phone);
				$phone_verified->setEmail($email);
				$phone_verified->setTimestamp(0);
				$phone_verified->setMode(PhoneVerified::MODE_CANDIDATE);
				
				$entity_manager->persist($phone_verified);
				$entity_manager->flush();
				
				$session->set('candidate_id', $candidate->getId());
				$session->set('candidate_name', $name);
				$session->set('candidate_lastname', $lastname);
				$session->set('candidate_dni', $dni);
				$session->set('candidate_email', $email);
				$session->set('candidate_phone', $phone);
				
				// Send mail with login link for admin
				$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
				 
				// pueda comentarle las incidencias (el administrador 
				$message = \Swift_Message::newInstance()
					->setSubject('Te has dado de alta como candidato')
					->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
					->setTo($email)
					->setBody(
							$this->renderView(
									'MunicipalesBundle:Mail:candidate_created.html.twig',
									array(
											'name' => $name,
											'admin_email' => $admin_candidacy->getEmail()
									)
							), 'text/html'
				);
				
				$this->get('mailer')->send($message);
				
				return $this->stepVerifyAction($request, $address_slug);
			}
		}

		$town = $admin_candidacy->getTown();
		$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
		$town_name = $province_repository->getMunicipalityName($town);
		
		return $this->render('MunicipalesBundle:Candidate:step1.html.twig', array(
				'address' => $address, 
				'town' => $town_name,
				'todate' => $candidacy_to,
				'fromdate' => $candidacy_from,
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function stepVerifyAction(Request $request = NULL, $address = NULL)
	{
		$session = $this->getRequest()->getSession();
		
		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');

		$address_slug = $this->get('slugify')->slugify($address);
		
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
		
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));		
		}
	
		$candidacy_total_days = $admin_candidacy->getTotalDays();
			
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}
		
		$form = $this->createForm(new CandidateStepVerifyType(), NULL, array(
				'action' => $this->generateUrl('candidate_step_verify', array('address' => $address_slug)),
				'method' => 'POST',
		)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$phone = $session->get('candidate_phone', NULL);
	
			if(empty($phone))
			{
				$form->addError(new FormError('El número de móvil no esta presente. ¿Sesión caducada?'));
				$ok = FALSE;
			}
	
			$email = $session->get('candidate_email', NULL);
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
				return $this->step2Action($request, $address);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step_verify.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address_slug,
		));
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function step2Action(Request $request = NULL, $address = NULL)
	{
		$session = $this->getRequest()->getSession();

		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		
		$address_slug = $this->get('slugify')->slugify($address);
		
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
		
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));
		}
		
		$candidacy_total_days = $admin_candidacy->getTotalDays();
			
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}
		
		$admin_id = $admin_candidacy->getId();
		$town = $admin_candidacy->getTown();
		
		$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
		$town_name = $province_repository->getMunicipalityName($town);
		
		$town_slug = $this->get('slugify')->slugify($town_name);
		
		$documents_path = 'docs/' . $town_slug . '/' . $admin_id;
		
		// Check loaded files by admin candidacy
		$loaded_files = array();

		$loaded_files['program']              = @file_exists($documents_path . '/program.pdf');
		$loaded_files['legal_conditions']     = @file_exists($documents_path . '/legal_conditions.pdf');
		$loaded_files['recall_term']          = @file_exists($documents_path . '/recall_term.pdf');
		$loaded_files['participatory_term']   = @file_exists($documents_path . '/participatory_term.pdf');
		$loaded_files['voter_conditions']     = @file_exists($documents_path . '/voter_conditions.pdf');
		$loaded_files['technical_constrains'] = @file_exists($documents_path . '/technical_constrains.pdf');
		
		// The admin didn't upload any document, so skipping to next step
		if(count($loaded_files) == 0)
		{
			return $this->step3Action($request, $address);
		}
		
		$candidate_id = $session->get('candidate_id', NULL);
		
		if(empty($candidate_id))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de candidato para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new CandidateStep2Type($loaded_files), NULL, array(
				'action' => $this->generateUrl('candidate_step2', array('address' => $address_slug)),
				'method' => 'POST',
			)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$documents_path = 'docs/' . $town_slug . '/' . $admin_id . '/candidate/' . $candidate_id;
			// getMaxFilesize()
			
			if($loaded_files['program'])
			{
				$program              = $form['program'];
				
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
							$program_data->move($documents_path, 'program.pdf');
						}
					}
				}
				else
				{
					$form->addError(new FormError('program pdf is not valid: ' . $program_data->getErrorMessage()));
					$ok = FALSE;
				}
			}
	
			if($loaded_files['legal_conditions'])
			{
				$legal_conditions     = $form['legal_conditions'];
				
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
							$legal_conditions_data->move($documents_path, 'legal_conditions.pdf');
						}
					}
				}
				else
				{
					$form->addError(new FormError('legal conditions pdf is not valid: ' . $legal_conditions_data->getErrorMessage()));
					$ok = FALSE;
				}
			}
	
			if($loaded_files['recall_term'])
			{
				$recall_term          = $form['recall_term'];
				
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
							$recall_term_data->move($documents_path, 'recall_term.pdf');
						}
					}
				}
				else
				{
					$form->addError(new FormError('recall term pdf is not valid: ' . $recall_term_data->getErrorMessage()));
					$ok = FALSE;
				}
			}
	
			if($loaded_files['participatory_term'])
			{
				$participatory_term   = $form['participatory_term'];
				
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
							$participatory_term_data->move($documents_path, 'participatory_term.pdf');
						}
					}
				}
				else
				{
					$form->addError(new FormError('participatory term pdf is not valid: ' . $participatory_term_data->getErrorMessage()));
					$ok = FALSE;
				}
			}
		
			if($loaded_files['voter_conditions'])
			{
				$voter_conditions     = $form['voter_conditions'];
				
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
							$voter_conditions_data->move($documents_path, 'voter_conditions.pdf');
						}
					}
				}
				else
				{
					$form->addError(new FormError('voter conditions pdf is not valid: ' . $voter_conditions_data->getErrorMessage()));
					$ok = FALSE;
				}
			}
	
			if($loaded_files['technical_constrains'])
			{
				$technical_constrains = $form['technical_constrains'];
				
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
							$technical_constrains_data->move($documents_path, 'technical_constrains.pdf');
						}
					}
				}
				else
				{
					$form->addError(new FormError('technical constrainss pdf is not valid: ' . $technical_constrains_data->getErrorMessage()));
					$ok = FALSE;
				}
			}
	
			if($ok)
			{
				if($loaded_files['program'])
				{
					if(!empty($program_data))
					{
						$session->set('program', $program_data->getClientOriginalName());
					}
				}
				
				if($loaded_files['legal_conditions'])
				{
					if(!empty($legal_conditions_data))
					{
						$session->set('legal_conditions', $legal_conditions_data->getClientOriginalName());
					}
				}
				 
				if($loaded_files['recall_term'])
				{
					if(!empty($recall_term_data))
					{
						$session->set('recall_term', $recall_term_data->getClientOriginalName());
					}
				}
				
				if($loaded_files['participatory_term'])
				{
					if(!empty($participatory_term_data))
					{
						$session->set('participatory_term', $participatory_term_data->getClientOriginalName());
					}
				}
				
				if($loaded_files['voter_conditions'])
				{
					if(!empty($voter_conditions_data))
					{
						$session->set('voter_conditions', $voter_conditions_data->getClientOriginalName());
					}
				}
				
				if($loaded_files['technical_constrains'])
				{
					if(!empty($technical_constrains_data))
					{
						$session->set('technical_constrains', $technical_constrains_data->getClientOriginalName());
					}
				}
	
				return $this->step3Action($request, $address);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step2_sign_documents.html.twig', array(
				'form' => $form->createView(),
				'address' => $address_slug,
				'documents_path' => $documents_path,
				'loaded_files' => $loaded_files,
		));
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function step3Action(Request $request = NULL, $address = NULL)
	{
		$session = $this->getRequest()->getSession();
	
		$address_slug = $this->get('slugify')->slugify($address);

		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');

		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));

		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));
		}
		
		$candidacy_total_days = $admin_candidacy->getTotalDays();
			
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}
		
		$candidate_id = $session->get('candidate_id', NULL);
		
		if(empty($candidate_id))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de candidato para cargar la dirección ' . $address_slug,
			));
		}
		
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
		
		$candidate = $candidate_repository->findOneById($candidate_id);
		
		if(empty($candidate))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe el candidato de administrador para cargar la dirección ' . $address_slug,
			));
		}
		
		$form = $this->createForm(new CandidateStep3Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step3', array('address' => $address_slug)),
				'method' => 'POST',
			)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$academic_level = $form['academic_level']->getData();
			$languages      = $form['languages']->getData();
	
			if($ok)
			{
				$session->set('candidate_academic_level', $academic_level);
				$session->set('candidate_languages', $languages);

				$candidate->setAcademicLevel($academic_level);
				$candidate->setLanguages($languages);
				 
				$entity_manager->persist($candidate);
				$entity_manager->flush();
				
				$form2 = $this->createForm(new CandidateStep4Type(), NULL, array(
						'action' => $this->generateUrl('candidate_step4', array('address' => $address_slug)),
						'method' => 'POST',
				));
	
				$form2->handleRequest($request);
	
				return $this->render('MunicipalesBundle:Candidate:step4_job_experience.html.twig', array(
						'address' => $address_slug,
						'form' => $form2->createView()
					)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step3_academic_level.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address_slug,
		));
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function step4Action(Request $request = NULL, $address = NULL)
	{
		$session = $this->getRequest()->getSession();
	
		$address_slug = $this->get('slugify')->slugify($address);
	
		$entity_manager = $this->getDoctrine()->getManager();
	
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
	
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
	
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));
		}
		
		$candidacy_total_days = $admin_candidacy->getTotalDays();
			
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}
	
		$candidate_id = $session->get('candidate_id', NULL);
	
		if(empty($candidate_id))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de candidato para cargar la dirección ' . $address_slug,
			));
		}
	
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
	
		$candidate = $candidate_repository->findOneById($candidate_id);
	
		if(empty($candidate))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe el candidato de administrador para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new CandidateStep4Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step4', array('address' => $address_slug)),
				'method' => 'POST',
		)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$job_experience = $form['job_experience']->getData();
	
			if(count($job_experience) > 3)
			{
				$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
				$ok = FALSE;
			}
			
			if($ok)
			{
				$session->set('candidate_job_experience', $job_experience);
	
				$candidate->setJobExperience($job_experience);

				$entity_manager->persist($candidate);
				$entity_manager->flush();
	
				$form2 = $this->createForm(new CandidateStep5Type(), NULL, array(
						'action' => $this->generateUrl('candidate_step5', array('address' => $address_slug)),
						'method' => 'POST',
				));
	
				$form2->handleRequest($request);
	
				return $this->render('MunicipalesBundle:Candidate:step5_town_activities.html.twig', array(
						'address' => $address_slug,
						'form' => $form2->createView()
					)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step4_job_experience.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address_slug,
		));
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function step5Action(Request $request = NULL, $address = NULL)
	{
		$session = $this->getRequest()->getSession();
	
		$address_slug = $this->get('slugify')->slugify($address);
	
		$entity_manager = $this->getDoctrine()->getManager();
	
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
	
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
	
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));
		}
		
		$candidacy_total_days = $admin_candidacy->getTotalDays();
			
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}
	
		$candidate_id = $session->get('candidate_id', NULL);
	
		if(empty($candidate_id))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de candidato para cargar la dirección ' . $address_slug,
			));
		}
	
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
	
		$candidate = $candidate_repository->findOneById($candidate_id);
	
		if(empty($candidate))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe el candidato de administrador para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new CandidateStep5Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step5', array('address' => $address_slug)),
				'method' => 'POST',
		)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$town_activities = $form['town_activities']->getData();
	
			if(count($town_activities) > 3)
			{
				$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
				$ok = FALSE;
			}
				
			if($ok)
			{
				$session->set('candidate_town_activities', $town_activities);
	
				$candidate->setTownActivities($town_activities);
	
				$entity_manager->persist($candidate);
				$entity_manager->flush();
	
				$form2 = $this->createForm(new CandidateStep6Type(), NULL, array(
						'action' => $this->generateUrl('candidate_step6', array('address' => $address_slug)),
						'method' => 'POST',
				));
	
				$form2->handleRequest($request);
	
				return $this->render('MunicipalesBundle:Candidate:step6_govern_priorities.html.twig', array(
						'address' => $address_slug,
						'form' => $form2->createView()
				)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step5_town_activities.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address_slug,
		));
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function step6Action(Request $request = NULL, $address = NULL)
	{
		$session = $this->getRequest()->getSession();
	
		$address_slug = $this->get('slugify')->slugify($address);
	
		$entity_manager = $this->getDoctrine()->getManager();
	
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
	
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
	
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));
		}
		
		$candidacy_total_days = $admin_candidacy->getTotalDays();
			
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}
	
		$candidate_id = $session->get('candidate_id', NULL);
	
		if(empty($candidate_id))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de candidato para cargar la dirección ' . $address_slug,
			));
		}
	
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
	
		$candidate = $candidate_repository->findOneById($candidate_id);
	
		if(empty($candidate))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe el candidato de administrador para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new CandidateStep6Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step6', array('address' => $address_slug)),
				'method' => 'POST',
		)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$govern_priorities = $form['govern_priorities']->getData();
	
			if(count($govern_priorities) > 3)
			{
				$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
				$ok = FALSE;
			}
	
			if($ok)
			{
				$session->set('candidate_govern_priorities', $govern_priorities);
	
				$candidate->setGovernPriorities($govern_priorities);
	
				$entity_manager->persist($candidate);
				$entity_manager->flush();
	
				return $this->step7Action($request, $address_slug);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step6_govern_priorities.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address_slug,
		));
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function step7Action(Request $request = NULL, $address = NULL)
	{
		$session = $this->getRequest()->getSession();
	
		$address_slug = $this->get('slugify')->slugify($address);
	
		$entity_manager = $this->getDoctrine()->getManager();
	
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
	
		$admin_candidacy = $admin_candidacy_repository->findOneBy(array('address' => $address_slug));
	
		if(empty($admin_candidacy))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección ' . $address_slug,
			));
		}
		
		$candidacy_total_days = $admin_candidacy->getTotalDays();
			
		if(!empty($candidacy_total_days))
		{
			return $this->render('MunicipalesBundle:Candidacy:missing_admin_id.html.twig', array(
					'error' => 'Error: una vez iniciada la votación no es posible registrar más candidatos o candidatas.',
			));
		}
	
		$candidate_id = $session->get('candidate_id', NULL);
	
		if(empty($candidate_id))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de candidato para cargar la dirección ' . $address_slug,
			));
		}
	
		$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
	
		$candidate = $candidate_repository->findOneById($candidate_id);
	
		if(empty($candidate))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe el candidato de administrador para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new CandidateStep7Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step7', array('address' => $address_slug)),
				'method' => 'POST',
		)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$public_values = $form['public_values']->getData();
			$motivation_text = $form['motivation_text']->getData();
			$town_activities_explanation = $form['town_activities_explanation']->getData();
			$additional_info = $form['additional_info']->getData();
			$url_info = $form['url_info']->getData();
	
			$profile_image = $form['profile_image'];
			
			if(count($public_values) > 3)
			{
				$form->addError(new FormError('Sólo se permiten un máximo de tres opciones seleccionadas'));
				$ok = FALSE;
			}
			
			$admin_id = $admin_candidacy->getId();
			$town = $admin_candidacy->getTown();
			
			$province_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Province');
			$town_name = $province_repository->getMunicipalityName($town);
			
			$town_slug = $this->get('slugify')->slugify($town_name);
			
			$documents_path = 'docs/' . $town_slug . '/' . $admin_id . '/candidate/' . $candidate_id . '/photo';
			
			$base_path = $this->getRequest()->getBasePath();
			$document_root = $this->getRequest()->server->get('DOCUMENT_ROOT');
			
			$fs = new Filesystem();
			
			if(!$fs->exists($documents_path))
			{
				try 
				{
					$fs->mkdir($base_path . '/' . $documents_path, 0700);
				} 
				catch (IOExceptionInterface $e) 
				{
					$form->addError(new FormError('An error occurred while creating your directory at: ' . $e->getPath()));
					$ok = FALSE;
				}
			}
			
			if($profile_image->isValid())
			{
				$profile_image_data = $profile_image->getData();
			
				if($profile_image_data->getClientMimeType() !== 'image/jpg' && $profile_image_data->getClientMimeType() !== 'image/jpeg' && $profile_image_data->getClientMimeType() !== 'image/png')
				{
					$form->addError(new FormError('MIMEType is not jpg or png, found: ' . $profile_image_data->getClientMimeType()));
					$ok = FALSE;
				}
			
				if($ok)
				{
					try
					{
						$profile_image_data->move($base_path . '/' . $documents_path, 'photo.jpg');
					}
					catch(FileException $e)
					{
						$form->addError(new FormError('Error uploading file in ' . $base_path . '/' . $documents_path . ': ' . $e->getMessage()));
						$ok = FALSE;
					}
				}
			}
			else
			{
				$form->addError(new FormError('profile image is not valid: ' . $profile_image_data->getErrorMessage()));
				$ok = FALSE;
			}
	
			if($ok)
			{
				$session->set('candidate_public_values', $public_values);
				$session->set('candidate_motivation_text', $motivation_text);
				$session->set('candidate_town_activities_explanation', $town_activities_explanation);
				$session->set('candidate_additional_info', $documents_path);
				$session->set('candidate_url_info', $url_info);
				
				$session->set('candidate_profile_image', $documents_path);
	
				$candidate->setPublicValues($public_values);
				$candidate->setMotivationText($motivation_text);
				$candidate->setTownActivitiesExplanation($town_activities_explanation);
				$candidate->setAdditionalInfo($additional_info);
				$candidate->setUrlInfo($url_info);
	
				$entity_manager->persist($candidate);
				$entity_manager->flush();
				
				$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
				
				$message = \Swift_Message::newInstance()
				->setSubject('Nuevo candidato registrado')
				->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
				->setTo($admin_candidacy->getEmail())
				->setBody(
						$this->renderView(
								'MunicipalesBundle:Mail:candidate_signup.html.twig',
								array('name' => $candidate->getName())
						), 'text/html'
				);
				 
				$this->get('mailer')->send($message);
	
				return $this->render('MunicipalesBundle:Candidate:final_step.html.twig', array(
						'address' => $address_slug,
					)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step7_public_values.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address_slug,
				'town_activities' => $candidate->getTownActivities(),
		));
	}
}
