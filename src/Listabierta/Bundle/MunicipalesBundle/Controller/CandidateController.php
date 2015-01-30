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

		$form = $this->createForm(new CandidateStep1Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step1', array('address' => $address)),
				'method' => 'POST',
			)
		);
		 
		$form->handleRequest($request);
		
		if ($form->isValid())
		{
			$name     = $form['name']->getData();
			$lastname = $form['lastname']->getData();
			$dni      = $form['dni']->getData();
			$email    = $form['email']->getData();
			$phone    = $form['phone']->getData();
			
			
			$entity_manager = $this->getDoctrine()->getManager();
			 
			// Store info in database AdminCandidacy
			$candidate = new Candidate();
			$candidate->setName($name);
			$candidate->setLastname($lastname);
			$candidate->setDni($dni);
			$candidate->setEmail($email);
			$candidate->setPhone($phone);
			 
			$entity_manager->persist($candidate);
			$entity_manager->flush();
			 
			// Store email and phone in database as pending PhoneVerified without timestamp
			$phone_verified = new PhoneVerified();
			$phone_verified->setPhone($phone);
			$phone_verified->setEmail($email);
			$phone_verified->setTimestamp(0);
			
			$entity_manager->persist($phone_verified);
			$entity_manager->flush();
			
			$session->set('candidate_id', $candidate->getId());
			$session->set('candidate_name', $name);
			$session->set('candidate_lastname', $lastname);
			$session->set('candidate_dni', $dni);
			$session->set('candidate_email', $email);
			$session->set('candidate_phone', $phone);
			
			$form2 = $this->createForm(new CandidateStepVerifyType(), NULL, array(
					'action' => $this->generateUrl('candidate_step_verify', array('address' => $address)),
					'method' => 'POST',
			));
			 
			$form2->handleRequest($request);
			
			return $this->render('MunicipalesBundle:Candidate:step_verify.html.twig', array(
					'form' => $form2->createView(),
					'address' => $address_slug,
				)
			);
			
		}

		return $this->render('MunicipalesBundle:Candidate:step1.html.twig', array(
				'address' => $address, 
				'town' => $admin_candidacy->getTown(),
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
	
		$form = $this->createForm(new CandidateStepVerifyType(), NULL, array(
				'action' => $this->generateUrl('candidate_step_verify', array('address' => $address_slug)),
				'method' => 'POST',
		)
		);
	
		$form->handleRequest($request);
	
		$ok = TRUE;
		if ($form->isValid())
		{
			$phone = $session->get('candidate_phone', array());
	
			if(empty($phone))
			{
				$form->addError(new FormError('El número de móvil no esta presente. ¿Sesión caducada?'));
				$ok = FALSE;
			}
	
			$email = $session->get('candidate_email', array());
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
				$form2 = $this->createForm(new CandidateStep2Type(), NULL, array(
						'action' => $this->generateUrl('candidate_step2', array('address' => $address)),
						'method' => 'POST',
				));
	
				$form2->handleRequest($request);
	
				$admin_id = $admin_candidacy->getId();
				$town = $admin_candidacy->getTown();
				
				$town_slug = $this->get('slugify')->slugify($town);
				
				$documents_path = 'docs/' . $town_slug . '/' . $admin_id;
				
				return $this->render('MunicipalesBundle:Candidate:step2_sign_documents.html.twig', array(
						'form' => $form2->createView(),
						'address' => $address_slug,
						'documents_path' => $documents_path,
					)
				);
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
		
		$admin_id = $admin_candidacy->getId();
		$town = $admin_candidacy->getTown();
		
		$town_slug = $this->get('slugify')->slugify($town);
		
		$documents_path = 'docs/' . $town_slug . '/' . $admin_id;
		
		$candidate_id = $session->get('candidate_id', NULL);
		
		if(empty($candidate_id))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'Sesión expirada. No existe el identificador de candidato para cargar la dirección ' . $address_slug,
			));
		}
	
		$form = $this->createForm(new CandidateStep2Type(), NULL, array(
				'action' => $this->generateUrl('candidate_step2', array('address' => $address_slug)),
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

			$documents_path = 'docs/' . $town_slug . '/' . $admin_id . '/candidate/' . $candidate_id;
	
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
	
				$form2 = $this->createForm(new CandidateStep3Type(), NULL, array(
						'action' => $this->generateUrl('candidate_step3', array('address' => $address_slug)),
						'method' => 'POST',
				));
	
				$form2->handleRequest($request);
	
				return $this->render('MunicipalesBundle:Candidate:step3_academic_level.html.twig', array(
						'errors' => $form->getErrors(),
						'form' => $form2->createView(),
						'address' => $address_slug,
					)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step2_sign_documents.html.twig', array(
				'form' => $form->createView(),
				'address' => $address_slug,
				'documents_path' => $documents_path,
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
	
				$form2 = $this->createForm(new CandidateStep7Type(), NULL, array(
						'action' => $this->generateUrl('candidate_step7', array('address' => $address_slug)),
						'method' => 'POST',
				));
	
				$form2->handleRequest($request);
	
				return $this->render('MunicipalesBundle:Candidate:step7_public_values.html.twig', array(
						'address' => $address_slug,
						'form' => $form2->createView()
				)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Candidate:step6_govern_priorities.html.twig', array(
				'form' => $form->createView(),
				'errors' => $form->getErrors(),
				'address' => $address_slug,
		));
	}
}
