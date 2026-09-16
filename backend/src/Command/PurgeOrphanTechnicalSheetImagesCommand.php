<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:fiches-techniques:purge-orphan-images',
    description: 'Supprime les images téléversées qui ne sont liées à aucune fiche technique.',
)]
final class PurgeOrphanTechnicalSheetImagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem $filesystem,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('older-than', null, InputOption::VALUE_REQUIRED, 'Âge minimal en secondes', '86400');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $olderThan = filter_var($input->getOption('older-than'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 3600]]);
        if (false === $olderThan) {
            $io->error('La durée minimale doit être un entier supérieur ou égal à 3600 secondes.');

            return Command::INVALID;
        }

        $usedPaths = $this->entityManager->createQuery(
            'SELECT fiche.lienImage FROM App\Entity\FicheTechnique fiche WHERE fiche.lienImage IS NOT NULL',
        )->getSingleColumnResult();
        $used = array_fill_keys($usedPaths, true);
        $directory = $this->projectDir.'/public/uploads/fiches-techniques';
        if (!is_dir($directory)) {
            $io->success('Aucun fichier orphelin à supprimer.');

            return Command::SUCCESS;
        }

        $threshold = time() - $olderThan;
        $removed = 0;
        foreach (new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS) as $file) {
            if (!$file instanceof \SplFileInfo) {
                continue;
            }

            if (!$file->isFile() || $file->getMTime() > $threshold) {
                continue;
            }

            $relativePath = '/uploads/fiches-techniques/'.$file->getFilename();
            if (isset($used[$relativePath])) {
                continue;
            }

            $this->filesystem->remove($file->getPathname());
            ++$removed;
        }

        $io->success(sprintf('%d image(s) orpheline(s) supprimée(s).', $removed));

        return Command::SUCCESS;
    }
}
