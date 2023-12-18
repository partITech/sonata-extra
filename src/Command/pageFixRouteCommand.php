<?php

namespace Partitech\SonataExtra\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
    name: 'sonata:extra:page-fix-route',
    description: 'Fix route of sonata page in order to make it work with sonata-extra multilang support',
)]
class pageFixRouteCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    ): void {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'help',
                'h',
                InputOption::VALUE_NONE,
                'There is no extra parameters for this command.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $help = $input->getOption('help');

        if ($help) {
            $io->success('There is no extra parameters for this command.');

            return Command::SUCCESS;
        }

        $pageClass = $this->parameterBag->get('sonata.page.page.class');
        $repository = $this->entityManager->getRepository($pageClass);

        $qb = $repository->createQueryBuilder('p');
        $qb->select('p.routeName')
            ->distinct();
        $routeNames = $qb->getQuery()->getResult();

        foreach ($routeNames as $routeName) {
            $route = $routeName['routeName'];
            $qb = $repository->createQueryBuilder('p')
                ->where('p.routeName = :route')
                ->setParameter('route', $route)
                ->orderBy('p.id', 'ASC');
            $pages = $qb->getQuery()->getResult();

            if (count($pages) > 0) {
                $translationFromId = $pages[0]->getId();
                foreach ($pages as $page) {
                    $page->setTranslationFromId($translationFromId);
                    $this->entityManager->persist($page);
                }
            }
        }

        $this->entityManager->flush();

        $io->success('Route names have been fixed successfully.');

        return Command::SUCCESS;
    }
}
