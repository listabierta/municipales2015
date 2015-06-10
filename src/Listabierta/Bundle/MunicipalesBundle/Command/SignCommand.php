<?php 
namespace Listabierta\Bundle\MunicipalesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Listabierta\Bundle\MunicipalesBundle\Lib\tractis\SymfonyTractisApi;

class MailTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('census:sign')
             ->setDescription('Fill the token field in census user entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	// Force to set host in command console
    	$this->getContainer()->get('router')->getContext()->setHost('censo.participasevilla.org');
    	
    	$logger = $this->getContainer()->get('logger');
    	
    	$logger->info('Executing command census:sign');

    	$entity_manager = $this->getContainer()->get('doctrine')->getManager();
    	
    	$consultation_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\Consultation');
    	$consultations = $consultation_repository->findAll();
    	
    	if(!empty($consultations))
    	{
    		foreach($consultations as $consultation)
    		{
    			$consultation_response_time = $consultation->getResponseTime();
    			$consultation_response_string = $consultation->getResponseString();
    			if(empty($consultation_response_time) || empty($consultation_response_string))
    			{
    				$ok = TRUE;
    				$census_user_id = $consultation->getCensusUserId();
    				$data           = $consultation->getData();
    				$token          = $consultation->getToken();
    				
    				$data_info = array();
    				
    				$data_info['census_user_id'] = $census_user_id;
    				$data_info['data']           = $data;
    				$data_info['token']          = $token;
    				
    				// Tractis TSA sign
    				
    				// Create an API Key here: https://www.tractis.com/webservices/tsa/apikeys
    				$tractis_api_identifier = $this->getContainer()->getParameter('tractis_api_identifier');
    				$tractis_api_secret     = $this->getContainer()->getParameter('tractis_api_secret');
    				 
    				// Fetch the chain sign TSA file
    				$tsa_cert_chain_file = $this->getContainer()->get('kernel')->locateResource('@MunicipalesBundle/Lib/tractis/chain.txt');
    				 
    				// Init the Symfony Tractis TSA Api
    				$symfony_tractis_api = new SymfonyTractisApi($tractis_api_identifier, $tractis_api_secret, $tsa_cert_chain_file);
    				 
    				// Sign a valid vote
    				try
    				{
    					$response = $symfony_tractis_api::sign(serialize($data_info));
    				}
    				catch(\Exception $e)
    				{
    					if($e->getMessage() == 'The Timestamp was not found')
    					{
    						$output->writeln('Tractis No disponible. Error grave en el firmado de voto TSA con consulta ID ' . $consultation->getId() . '. Respuesta de tractis: The Timestamp was not found');
    						$ok = FALSE;
    					}
    					else
    					{
    						$output->writeln('Error grave en el firmado de voto TSA con consulta ID ' . $consultation->getId() . '. Respuesta de tractis: ' . $e->getMessage());
    						$ok = FALSE;
    					}
    				}
    				 
    				// Check response data
    				if(empty($response))
    				{
    					$output->writeln('Error con consulta ID ' . $consultation->getId() . ' en el firmado de voto TSA. Respuesta vacía');
    					$ok = FALSE;
    				}
    				
    				if(empty($response['response_string']))
    				{
    					$output->writeln('Error con consulta ID ' . $consultation->getId() . ' en el firmado de voto TSA. Respuesta con cadena vacía');
    					$ok = FALSE;
    				}
    				
    				if(empty($response['response_time']))
    				{
    					$output->writeln('Error con consulta ID ' . $consultation->getId() . ' en el firmado de voto TSA. Respuesta con tiempo vacía');
    					$ok = FALSE;
    				}
    				
    				if($ok)
    				{
	    				// Fetch the data response if valid
	    				$response_string = $response['response_string'];
	    				$response_time   = $response['response_time'];
	    				 
	    				// Store the sign TSA result in database
	    				$consultation->setResponseString($response_string);
	    				$consultation->setResponseTime($response_time);
	    				 
	    				$entity_manager->persist($consultation);
	    				$entity_manager->flush();
	    				
	    				$census_user_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\CensusUser');
	    				$census_user = $census_user_repository->findOneById($census_user_id);
	    				
	    				if(!empty($census_user))
	    				{
	    					$census_user_name  = $census_user->getName();
	    					$census_user_email = $census_user->getEmail();
	    				
	    					if(!empty($census_user_email))
	    					{
								try
								{
									// Send mail with vote signed
									$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'censo.participasevilla.org';
									
									$message = \Swift_Message::newInstance()
									->setSubject('Tu voto ha sido emitido correctamente')
									->setFrom('info@' . rtrim($host, '.'), 'Informacion Censo')
									->setTo($census_user_email)
									->setBody(
											$this->getContainer()->get('templating')->render(
													'MunicipalesBundle:Mail:vote_finished.html.twig',
													array(
														'name' => $census_user_name,
			    										'response_string' => $response_string,
			    										'response_time' => $response_time,
													)
											), 'text/html'
									);
									 
									$this->getContainer()->get('mailer')->send($message);
									
			    					$output->writeln('Vote signed. Sent mail to [' . $census_user_email .  '] for user ID ' . $census_user_id . '.');
		    					}
		    					catch(\Exception $e)
		    					{
		    						$output->writeln($census_user_email . ' error: ' . $e->getMessage());
		    					}
	    					}
	    				}
    				}
    			}
    		}
    	}
    	
        $output->writeln('Done.');
    }
}