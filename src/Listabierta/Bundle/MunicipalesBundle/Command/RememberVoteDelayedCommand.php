<?php 
namespace Listabierta\Bundle\MunicipalesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RememberVoteDelayedCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('census:remembervotedelayed')
             ->setDescription('Send a remember email for remaining voters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	// Force to set host in command console
    	$this->getContainer()->get('router')->getContext()->setHost('censo.participasevilla.org');
    	
    	$logger = $this->getContainer()->get('logger');
    	
    	$logger->info('Executing command census:remembervotedelayed');

    	$entity_manager = $this->getContainer()->get('doctrine')->getManager();
    	
    	$census_user_repository = $entity_manager->getRepository('Listabierta\Bundle\MunicipalesBundle\Entity\CensusUser');
    	$census_users = $census_user_repository->findAll();
    	
    	if(!empty($census_users))
    	{
    		foreach($census_users as $census_user)
    		{
    			$census_user_token = $census_user->getToken();
    			if(!empty($census_user_token))
    			{
					//if($census_user->getId() == 1)
					//{
						try
						{
							// Send mail with login link for admin
							$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'censo.participasevilla.org';
							
							$message = \Swift_Message::newInstance()
							->setSubject('Ampliación de plazo para la votación de investidura de la alcaldía de Sevilla')
							->setFrom('info@' . rtrim($host, '.'), 'Participa Sevilla')
							->setTo($census_user->getEmail())
							->setBody(
									$this->getContainer()->get('templating')->render(
											'MunicipalesBundle:Mail:remember_vote_delayed.html.twig',
											array(
												'token' => $census_user_token,
											)
									), 'text/html'
							);
							 
							$this->getContainer()->get('mailer')->send($message);
							
	    					$output->writeln('Sent remember vote mail with token [' . $census_user_token .  '] for user ID ' . $census_user->getId() . '.');
    					}
    					catch(\Exception $e)
    					{
    						$output->writeln($census_user->getEmail() . ' error: ' . $e->getMessage());
    					}
					//}
    			}
    		}
    	}
    	
        $output->writeln('Done.');
    }
}