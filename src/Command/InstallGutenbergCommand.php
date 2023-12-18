<?php

namespace Partitech\SonataExtra\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'sonata:extra:install-gutenberg',
    description: 'Downloads and installs the Gutenberg editor',
)]
class InstallGutenbergCommand extends Command
{

    private LoggerInterface $logger;

    #[Required]
    public function autowireDependencies(
        LoggerInterface $logger,
    ): void {
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force download and installation of Gutenberg editor even if already installed.'
            )
        ;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');

        $destinationDir = __DIR__.'/../../Resources/public/';
        $destination = $destinationDir.'gutenberg.zip';
        $extractedPath = $destinationDir.'gutenberg';

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        if (file_exists($extractedPath) && !$force) {
            $io->note('Gutenberg editor is already installed.');
            $shouldUpdate = $io->confirm('Would you like to update it?');

            if (!$shouldUpdate) {
                $io->success('No changes were made.');

                return Command::SUCCESS;
            }
        }

        $url = 'https://github.com/Automattic/isolated-block-editor/archive/refs/heads/trunk.zip';

        $io->note('Downloading Gutenberg editor...');
        $client = HttpClient::create();
        $response = $client->request('GET', $url);
        try {
            file_put_contents($destination, $response->getContent());
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Erreur lors de la sauvegarde du contenu : ' . $e->getMessage());

        }

        $io->success('Gutenberg editor downloaded successfully.');

        $io->note('Extracting Gutenberg editor...');
        $zip = new \ZipArchive();
        $zip->open($destination);
        $zip->extractTo($extractedPath);
        $zip->close();

        $io->success('Gutenberg editor extracted and installed successfully.');

        // Deleting the ZIP file
        $io->note('Deleting the ZIP file...');
        if (unlink($destination)) {
            $io->success('ZIP file deleted successfully.');
        } else {
            $io->error('Failed to delete ZIP file.');
        }

        return Command::SUCCESS;
    }
}
