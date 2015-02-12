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
use Listabierta\Bundle\MunicipalesBundle\Form\Type\TownStepVerifyType;

use Symfony\Component\Form\FormError;

class TownController extends Controller
{
	public function indexAction(Request $request = NULL)
	{
		$this->step1Action($request);
	}
	
	private function verifyAdminAddress($address)
	{
		$entity_manager = $this->getDoctrine()->getManager();
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
			
		if(empty($admin_candidacy) || empty($address))
		{
			return $this->render('MunicipalesBundle:Candidate:step1_unknown.html.twig', array(
					'error' => 'No existe la candidatura de administrador para cargar la dirección <b>' . $address . '</b>',
			));
		}
	}
	
	public function vote1Action($address = NULL, Request $request = NULL)
	{
		$session = $this->getRequest()->getSession();
		$entity_manager = $this->getDoctrine()->getManager();
		
		$this->verifyAdminAddress($address);
		
		$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
		$admin_candidacy = $admin_candidacy_repository->findOneByAddress($address);
		
		$town = $admin_candidacy->getTown();
		
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
			$username = $form['username']->getData();
			$password = $form['password']->getData();
			$email    = $form['email']->getData();
			$phone    = $form['phone']->getData();
			
			$entity_manager = $this->getDoctrine()->getManager();
			$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
			
			$voter_username = $voter_repository->findOneBy(array('username' => $username));
			 
			if(!empty($voter_username))
			{
				$form->addError(new FormError('Ya existe un usuario votante registrado con el nombre de usuario ' . $username));
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
				$voter->setUsername($username);
				 
				$factory = $this->get('security.encoder_factory');
				$encoder = $factory->getEncoder($voter);
				$encodedPassword = $encoder->encodePassword($password, $voter->getSalt());
			
				$voter->setPassword($encodedPassword);
				$voter->setEmail($email);
				$voter->setPhone($phone);
				 
				$entity_manager->persist($voter);
				$entity_manager->flush();
				 
				// Store email and phone in database as pending PhoneVerified without timestamp
				$phone_verified = new PhoneVerified();
				$phone_verified->setPhone($phone);
				$phone_verified->setEmail($email);
				$phone_verified->setTimestamp(0);
			
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
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function voteVerifyAction(Request $request = NULL, $address = NULL)
	{
		$this->verifyAdminAddress($address);
		
		$session = $this->getRequest()->getSession();
	
		$entity_manager = $this->getDoctrine()->getManager();
		
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
				$form2 = $this->createForm(new TownStep2Type(), NULL, array(
						'action' => $this->generateUrl('town_candidacy_vote_step2', array('address' => $address)),
						'method' => 'POST',
				));
		
				$form2->handleRequest($request);
		
				return $this->render('MunicipalesBundle:Town:step2.html.twig', array(
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
		$this->verifyAdminAddress($address);
		
		$session = $this->getRequest()->getSession();
		
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
		
		$form = $this->createForm(new TownStep2Type(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_vote_step2', array('address' => $address)),
				'method' => 'POST',
			)
		);
			
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
			$academic_level = $form['academic_level']->getData();
			$languages      = $form['languages']->getData();
			
			if($ok)
			{
				$session->set('voter_academic_level', $academic_level);
				$session->set('voter_languages', $languages);
			
				$voter->setAcademicLevel($academic_level);
				$voter->setLanguages($languages);
					
				$entity_manager->persist($voter);
				$entity_manager->flush();
			
				$form2 = $this->createForm(new TownStep3Type(), NULL, array(
						'action' => $this->generateUrl('town_candidacy_vote_step3', array('address' => $address)),
						'method' => 'POST',
				));
			
				$form2->handleRequest($request);
			
				return $this->render('MunicipalesBundle:Town:step3.html.twig', array(
						'address' => $address,
						'form' => $form2->createView()
					)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Town:step2.html.twig', array(
				'address' => $address,
				'form' => $form->createView(),
				'errors' => $form->getErrors()
		));
	}

	public function vote3Action($address = NULL, Request $request = NULL)
	{
		$this->verifyAdminAddress($address);
	
		$session = $this->getRequest()->getSession();
	
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
	
		$form = $this->createForm(new TownStep3Type(), NULL, array(
				'action' => $this->generateUrl('town_candidacy_vote_step3', array('address' => $address)),
				'method' => 'POST',
		)
		);
			
		$form->handleRequest($request);
	
		if ($form->isValid())
		{
			$job_experience = $form['job_experience']->getData();
				
			if($ok)
			{
				$session->set('voter_job_experience', $job_experience);
					
				$voter->setJobExperience($job_experience);
					
				$entity_manager->persist($voter);
				$entity_manager->flush();
					
				$form2 = $this->createForm(new TownStep4Type(), NULL, array(
						'action' => $this->generateUrl('town_candidacy_vote_step4', array('address' => $address)),
						'method' => 'POST',
				));
					
				$form2->handleRequest($request);
					
				return $this->render('MunicipalesBundle:Town:step4.html.twig', array(
						'address' => $address,
						'form' => $form2->createView()
				)
				);
			}
		}
	
		return $this->render('MunicipalesBundle:Town:step3.html.twig', array(
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
