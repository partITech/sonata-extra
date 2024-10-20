<?php

namespace Partitech\SonataExtra\Command;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Controller\Admin\PageAdminController;
use Partitech\SonataExtra\Service\TranslateObjectService;
use Partitech\SonataExtra\SmartService\TranslationCreateTemplateService;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;


#[AsCommand(
    name: 'sonata:extra:translation-import-template',
    description: 'Import your content from a file structure exported from sonata:extra:translation-create-template',
)]
class TranslateContentImportTemplateCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private Pool $adminPool;
    private TranslationCreateTemplateService $TranslationCreateTemplateService;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        TranslationCreateTemplateService $TranslationCreateTemplateService,
        Pool $adminPool,
        PageAdminController $PageAdminController
    ): void {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->TranslationCreateTemplateService = $TranslationCreateTemplateService;
        $this->adminPool = $adminPool;
        $this->PageAdminController = $PageAdminController;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'help',
                'h',
                InputOption::VALUE_NONE,
                'Display this help message.'
            )
            ->addOption(
                'service',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify the entity to translate by its admin service.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        putenv('isCreateTemplateCmd=true');


        $help = $input->getOption('help');
        $service = $input->getOption('service');
        if ($help) {
            $io->success('Usage: bin/console sonata:extra:translation-import-template --service="Partitech\SonataExtra\Admin\ArticleAdmin"');
            return Command::SUCCESS;
        }



        if (!$service) {
            $io->error('--service options is required. Usage: bin/console sonata:extra:translation-import-template --service="Partitech\SonataExtra\Admin\ArticleAdmin"');
            return Command::INVALID;
        }

        $adminService = $this->adminPool->getAdminByAdminCode($service);
        $entityClass = $adminService->getClass();

        $siteClass = $this->parameterBag->get('sonata.page.site.class');
        $siteRepository = $this->entityManager->getRepository($siteClass);
        $sites = $siteRepository->findAll();
        $siteLocales = [];
        foreach ($sites as $s) {
            $siteLocales[$s->getId()] = $s->getLocale();
        }

        $serviceName=$this->TranslationCreateTemplateService->getClassNameByEntityClass($entityClass);
        $translationPath=$this->TranslationCreateTemplateService->getTemplateTranslationPath($serviceName);
        $translationlist=$this->TranslationCreateTemplateService->readTemplateTranslationPath($translationPath);

        $progressBar = new ProgressBar($output, 100);
        $format="\n\t\t<fg=white;bg=cyan> %status:-45s%</>\n\n";
        $format.="\t\t[%bar%] %percent:3s%%\n\n";
        $format.="\t\t\n";
        $progressBar->setFormat($format);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>𝄞</>");

        $progressBar->setProgress(0);
        $progressBar->setMessage('Initialisation', 'status');
        //$progressBar->setMessage('waiting', 'current_job');
        $progressBar->start();




        $jobs=0;
        $total_job=count($translationlist);
        foreach ($translationlist as $itemId) {


            $progress_percent=round((100/$total_job)*$jobs);
            $progressBar->setMessage('<fg=green>'.$jobs.' / '.$total_job.' tasks</> ', 'status');

            $progressBar->setProgress($progress_percent);
            $progressBar->display();
            if($service=="sonata.page.admin.page"){
                //$this->TranslateObjectService->createTranslation($item->getId(), $referenceSite, $site_id, $service);
                //$this->PageAdminController->createPageFromLocaleAction($item->getId(), $referenceSite, $site_id);
            }else{
                 $this->TranslationCreateTemplateService->importTranslation(
                     $translationPath,
                     $itemId,
                     $entityClass);


            }

            $jobs++;

        }

        $progressBar->finish();
        $io->success('Items have successfully been imported');
        return Command::SUCCESS;
    }


}