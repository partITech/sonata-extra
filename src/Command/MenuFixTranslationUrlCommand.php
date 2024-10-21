<?php

namespace Partitech\SonataExtra\Command;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Service\LocaleService;
use Partitech\SonataExtra\Service\TranslateObjectService;
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
    name: 'sonata:extra:fix-menu-url',
    description: 'Fix the url from a fresh translated menu',
)]
class MenuFixTranslationUrlCommand extends Command
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
        TranslateObjectService $translateObjectService,
        Pool                   $adminPool,
        LocaleService          $LocaleService
    ): void {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->translateObjectService = $translateObjectService;
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
            )
            ->addOption(
                'menu',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify the menu id to fix'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $help = $input->getOption('help');
        $menuId = $input->getOption('menu');

        if ($help) {
            $io->success('Usage: bin/console sonata:extra:fix-menu-url --menu=2');
            return Command::SUCCESS;
        }

        $menuClass = $this->parameterBag->get('sonata_menu.entity.menu');
        $menuRepository = $this->entityManager->getRepository($menuClass);

        $menu = $menuRepository->findOneBy(['id'=>$menuId]);

        if(!empty($menu)){
            $menuItems=$menu->getMenuItems();
        }else{
            return Command::SUCCESS;
        }



        $progressBar = new ProgressBar($output, 100);
        $format="\n\t\t<fg=white;bg=cyan> %status:-45s%</>\n\n";
        $format.="\t\t[%bar%] %percent:3s%%\n\n";
        $format.="\t\t%current_item%\n";

        $format.="\t\t\n";
        $progressBar->setFormat($format);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>𝄞</>");

        $progressBar->setProgress(0);
        $progressBar->setMessage('Initialisation', 'status');
        $progressBar->start();

        $jobs=0;
        $total_job=count($menuItems);
        foreach ($menuItems as $MenuItem) {
            $jobs++;
            $progressBar->setMessage('<fg=green>'.$jobs.' / '.$total_job.' tasks</> ', 'status');
            $progressBar->setMessage('<fg=green>#'.$MenuItem->getId().' : '.$MenuItem->getUrl().'</>', 'current_item');
            $progress_percent=round((100/$total_job)*$jobs);
            $progressBar->setProgress($progress_percent);
            $progressBar->display();

            $this->localeService->fixMenuItemUrl($MenuItem);
        }

        $progressBar->finish();
        $progressBar->setMessage('<fg=green>Menu '.$menuId.' have successfully been fixed</>', 'current_item');
        $progressBar->display();

        return Command::SUCCESS;
    }
}