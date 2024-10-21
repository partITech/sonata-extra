<?php

namespace Partitech\SonataExtra\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Partitech\SonataExtra\Service\TranslateObjectService;
use Sonata\AdminBundle\Admin\Pool;
use Partitech\SonataExtra\Service\LocaleService;
use Partitech\SonataExtra\Entity\Article;

#[AsCommand(
    name: 'sonata:extra:fix-article-classification',
    description: 'Translate your content into a given site locale',
)]
class ArticleFixClassificationCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private Pool $adminPool;
    private LocaleService $localeService;

    private TranslateObjectService $translateObjectService;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface  $parameterBag,
        TranslateObjectService $TranslateObjectService,
        Pool                   $adminPool,
        LocaleService          $LocaleService
    ): void
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->translateObjectService = $TranslateObjectService;
        $this->adminPool = $adminPool;
        $this->localeService = $LocaleService;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'help',
                'h',
                InputOption::VALUE_NONE,
                'Display this help message.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $help = $input->getOption('help');

        if ($help) {
            $io->success('Usage: bin/console sonata:extra:translate-content --site=1,2,3 --reference-site=1  --fqcn=Partitech\SonataExtra\Entity\Article');
            return Command::SUCCESS;
        }

        $fqcnRepository = $this->entityManager->getRepository(Article::class);
        $fqcnList = $fqcnRepository->findAll();


        $progressBar = new ProgressBar($output, 100);
        $format = "\n\t\t<fg=white;bg=cyan> %status:-45s%</>\n\n";
        $format .= "\t\t[%bar%] %percent:3s%%\n\n";
        $format .= "\t\t%current_item%\n";

        $format .= "\t\t\n";
        $progressBar->setFormat($format);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>𝄞</>");

        $progressBar->setProgress(0);
        $progressBar->setMessage('Initialisation', 'status');
        $progressBar->start();

        $jobs = 0;
        $total_job = count($fqcnList);
        foreach ($fqcnList as $item) {
            $jobs++;
            $progressBar->setMessage('<fg=green>' . $jobs . ' / ' . $total_job . ' tasks</> ', 'status');
            $progressBar->setMessage('<fg=green>#' . $item->getId() . ' : ' . $item . '</>', 'current_item');
            $progress_percent = round((100 / $total_job) * $jobs);
            $progressBar->setProgress($progress_percent);
            $progressBar->display();
            $entity = $this->localeService->fixEntityCategoryTag($item);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }

        $progressBar->finish();
        $progressBar->setMessage('<fg=green>Article have successfully been fixed</>', 'current_item');
        $progressBar->display();

        return Command::SUCCESS;
    }
}