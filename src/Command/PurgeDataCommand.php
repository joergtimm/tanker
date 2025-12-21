<?php

namespace App\Command;

use App\Entity\OpeningTime;
use App\Entity\Price;
use App\Entity\Station;
use App\Entity\StationDetail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:data:purge',
    description: 'Löscht alle Stationen, Preise und Öffnungszeiten aus der Datenbank.',
)]
class PurgeDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Erzwingt das Löschen ohne Bestätigung')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force')) {
            if (!$io->confirm('Sind Sie sicher, dass Sie alle Stationen, Preise und Öffnungszeiten löschen möchten? Dieser Vorgang kann nicht rückgängig gemacht werden.', false)) {
                $io->note('Vorgang abgebrochen.');
                return Command::SUCCESS;
            }
        }

        $io->section('Lösche Daten...');

        try {
            // Wir nutzen DQL DELETE, um sicherzustellen, dass Doctrine-Events (falls vorhanden) beachtet werden,
            // oder wir nutzen direkt SQL für maximale Performance.
            // Da wir alles löschen wollen, ist SQL am effizientesten.

            $connection = $this->entityManager->getConnection();
            $platform = $connection->getDatabasePlatform();

            // Deaktiviere Foreign Key Checks (falls von der DB unterstützt, z.B. MySQL/SQLite)
            if (method_exists($platform, 'getSupportsForeignKeyConstraints') && $platform->getSupportsForeignKeyConstraints()) {
                 // Dies ist plattformabhängig. Für eine generische Lösung löschen wir in der richtigen Reihenfolge.
            }

            $io->text('Lösche Preise...');
            $this->entityManager->createQuery('DELETE FROM ' . Price::class)->execute();

            $io->text('Lösche Öffnungszeiten...');
            $this->entityManager->createQuery('DELETE FROM ' . OpeningTime::class)->execute();

            $io->text('Lösche Stationsdetails...');
            $this->entityManager->createQuery('DELETE FROM ' . StationDetail::class)->execute();

            $io->text('Lösche Stationen...');
            $this->entityManager->createQuery('DELETE FROM ' . Station::class)->execute();

            $this->entityManager->flush();
            $this->entityManager->clear();

            $io->success('Alle Daten wurden erfolgreich gelöscht.');
        } catch (\Exception $e) {
            $io->error('Fehler beim Löschen der Daten: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
