<?php
namespace Listabierta\Bundle\MunicipalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified;

class ManagerController extends Controller
{

	public function indexAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			return new Response('<ul>
									<li><a href="' . $this->generateUrl('manager_verify_phone', array('phone' => 1234)) . '">Verify phone 1234</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_phones') . '">Purge phones</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_candidacies') . '">Purge candidacies</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_candidates') . '">Purge candidates</a></li>
									<li><a href="' . $this->generateUrl('manager_purge_voters') . '">Purge voters</a></li>
									</ul>', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function verifyPhoneAction(Request $request = NULL, $phone = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$result = '';
			$entity_manager = $this->getDoctrine()->getManager();
			$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
				
			$phones_verified = $phone_verified_repository->findBy(array('phone' => $phone));
			
			foreach($phones_verified as $phone_verified)
			{	
				if(!empty($phone_verified) && $phone_verified->getTimestamp() == 0)
				{
					$email = $phone_verified->getEmail();
					$phone_verified->setTimestamp(time());
					
					$entity_manager->persist($phone_verified);
					$entity_manager->flush();
					
					$result .= 'Verified ' . $phone . ' with mail ' . $email . '<br />';
				}
			}
			
			return new Response('OK ' . $result, 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}

	public function purgePhonesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$phone_verified_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\PhoneVerified');
				
			$phones = $phone_verified_repository->findAll();
				
			if(!empty($phones))
			{
				foreach($phones as $phone)
				{
					$entity_manager->remove($phone);
				}
	
				$entity_manager->flush();
			}
				
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function purgeCandidaciesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
			
			$candidacies = $admin_candidacy_repository->findAll();
			
			if(!empty($candidacies))
			{
				foreach($candidacies as $candidacy)
				{
					$entity_manager->remove($candidacy);
				}
				
				$entity_manager->flush();
			}
			
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function purgeVotersAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
				
			$voters = $voter_repository->findAll();
				
			if(!empty($voters))
			{
				foreach($voters as $voter)
				{
					$entity_manager->remove($voter);
				}
			
				$entity_manager->flush();
			}

			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function purgeCandidatesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			
			$entity_manager = $this->getDoctrine()->getManager();
			$candidate_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Candidate');
				
			$candidates = $candidate_repository->findAll();
				
			if(!empty($candidates))
			{
				foreach($candidates as $candidate)
				{
					$entity_manager->remove($candidate);
				}
			
				$entity_manager->flush();
			}
			
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}
	
	public function listCandidaciesAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'dev')
		{
			$entity_manager = $this->getDoctrine()->getManager();
			$admin_candidacy_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\AdminCandidacy');
				
			$candidacies = $admin_candidacy_repository->findAll();
				
			if(!empty($candidacies))
			{
				
			}
		}
		else
		{
			return new Response('Access only enabled in dev mode', 403);
		}
	}		
	
	/**
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function spamTestAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'prod' && FALSE)
		{
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
			 
			$admin_email = $this->container->getParameter('admin_email');
			
			$message = \Swift_Message::newInstance()
			->setSubject('Prueba de correo a ' . $admin_email . ' con host ' . $host)
			->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
			->setTo($admin_email)
			->setBody(
					$this->renderView(
							'MunicipalesBundle:Mail:candidate_rejected.html.twig',
							array(
									'name' => $host,
									'admin_email' => $admin_email
							)
					), 'text/html'
			);
			 
			$this->get('mailer')->send($message);
			
			return new Response('OK', 200);
		}
		else
		{
			return new Response('Access only enabled in prod mode', 403);
		}
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function launchMailAction(Request $request = NULL)
	{
		$min_counter = isset($_REQUEST['min']) ? intval($_REQUEST['min']) : 0;
		$max_counter = isset($_REQUEST['max']) ? intval($_REQUEST['max']) : 100;
		$output = NULL;
		
		if($this->container->getParameter('kernel.environment') == 'prod' && FALSE)
		{
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

			$subject = 'Miguel Prados de listAbierta.org, ¿Tenéis forma jurídica para presentaros a las elecciones municipales de mayo 2.015? Podéis contar con estas herramientas';
			$render_view = $this->renderView('MunicipalesBundle:Mail:launch.html.twig', array());

			$document_root = $this->getRequest()->server->get('DOCUMENT_ROOT');
			$csv_file = $document_root . '/suscribers.csv';

			$parsed = file($csv_file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
			
			if($parsed === FALSE)
			{
				echo 'Filename ' . $csv_file . ' could not be parsed from CSV format.<br />';
			}
			else
			{
				$csv = array_map('str_getcsv', $parsed);
			
				if(empty($csv))
				{
					echo 'Filename is empty.<br />';
				}
				else
				{
					// Each row contains a record.
					$headers = array_slice($csv, 0, 1);
					$content = array_slice($csv, 1 + $min_counter, $max_counter); // Skip headers
					if(!empty($content))
					{
						$counter = 0;
						foreach($content as $line => $row)
						{
								$suscriber_mail    = $row[0];
								//$suscriber_name    = $row[1];
								try
								{
									$message = \Swift_Message::newInstance()
									->setSubject($subject)
									->setFrom('noreply@' . rtrim($host, '.'), 'No responder')
									->setTo($suscriber_mail)
									->setBody($render_view, 'text/html');
								
									$this->get('mailer')->send($message);
									$output .= $suscriber_mail . ' ✓<br/>';
								}
								catch(\Exception $e)
								{
									$output .= $suscriber_mail . ' error: ' . $e->getMessage() . '<br/>';
								}
						}
					}
				}
			}

			return new Response('OK<br/><br/>' . $output , 200);
		}
		else
		{
			return new Response('Access only enabled in prod mode', 403);
		}
	}
	
	/**
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function deleteVotesNoSealedAction(Request $request = NULL)
	{
		if($this->container->getParameter('kernel.environment') == 'prod' && FALSE)
		{
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
		
			$subject = 'Tu voto en ' . $host;

			$entity_manager = $this->getDoctrine()->getManager();
			$voter_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Voter');
			
			$voters = $voter_repository->findAll();
			
			// Process each vote
			foreach($voters as $voter)
			{
				$vote_info = $voter->getVoteInfo();
				$vote_response_string = $voter->getVoteResponseString();
				$vote_response_time   = $voter->getVoteResponseTime();
				
				// Detect if we have some vote emitted but not signed by tractis to delete it
				if(empty($vote_info) && empty($vote_response_string) && empty($vote_response_time))
				{
					try
					{
						$message = \Swift_Message::newInstance()
						->setSubject($subject)
						->setFrom('candidaturas@' . rtrim($host, '.'), 'Candidaturas')
						->setTo($voter->getEmail())
						->setBody(
								$this->renderView(
										'MunicipalesBundle:Mail:vote_deleted.html.twig',
										array('voter' => $voter)
								), 'text/html'
						);
						
						$this->get('mailer')->send($message);
						
						$output .= 'Mail send to voter ID ' . $voter->getId() . '<br />';
						
						// Delete the voter
						$entity_manager->remove($voter);
					}
					catch(\Exception $e)
					{
						$output .= $voter->getEmail() . ' error: ' . $e->getMessage() . '<br/>';
					}
				}
			}
			
			$entity_manager->flush();
			
			return new Response('OK<br/><br/>' . $output , 200);
		}
		else
		{
			return new Response('Access only enabled in prod mode', 403);
		}
	}
}
