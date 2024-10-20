<?php

namespace Partitech\SonataExtra\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Partitech\SonataExtra\Event\ImportWpProgressEvent;
use Partitech\SonataExtra\Service\ImportWordpress\Import;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'sonata-extra:wordpress-importer',
    description: 'Add a short description for your command',
)]
class WordpressImporterCommand extends Command
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Import $import
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'URL de l\'API')
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'Utilisateur de l\'API')
            ->addOption('token', null, InputOption::VALUE_REQUIRED, 'Token de l\'API')
        ;
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
{
        $progressBar = new ProgressBar($output, 100);
        $progressBar->setFormat(
            "
            <fg=white;bg=cyan> %status:-45s%</>\n
            [%bar%] %percent:3s%%\n
            %current_job%
            "
        );
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>𝄞</>");

        
        $progressBar->setProgress(0);
        $progressBar->setMessage('Initialisation', 'status');
        $progressBar->setMessage('waiting', 'current_job');
        $progressBar->start();
        
        $this->eventDispatcher->addListener(ImportWpProgressEvent::NAME, function (ImportWpProgressEvent $event) use ($progressBar) {
            
            if(is_numeric($event->getProgress())){
                $progressBar->setProgress($event->getProgress());
            }
            
            if($event->getProgress()=== 100){
                $progressBar->finish();
            }
            
            if(!is_null($event->getMessage())){
                $progressBar->setMessage($event->getMessage(), 'status');
            }
            if(!is_null($event->getCurrentJob())){
                $progressBar->setMessage($event->getCurrentJob(), 'current_job');
            }
            
            $progressBar->display();

        });

        $url = $input->getOption('url');
        $user = $input->getOption('user');
        $token = $input->getOption('token');

        $this->import
            ->setUrl($url)
            ->setApiUser($user)
            ->setApiToken($token)
            ->execute();

        return Command::SUCCESS;
    }
}
