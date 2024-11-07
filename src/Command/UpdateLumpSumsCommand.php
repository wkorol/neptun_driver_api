<?php

namespace App\Command;

use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update-lump-sums', description: 'Updates LumpSums when expire date is reached')]
class UpdateLumpSumsCommand extends Command
{
    private HotelRepository $hotelRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(HotelRepository $hotelRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->hotelRepository = $hotelRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hotels = $this->hotelRepository->findAll();

        foreach ($hotels as $hotel) {
            if ($hotel->getLumpSumsExpireDate() !== null && $hotel->getLumpSumsExpireDate() <= new \DateTimeImmutable()) {
                $hotel->updateLumpSums($hotel->getNewLumpSums());
                $hotel->updateNewLumpSums(null);
                $hotel->updateLumpSumsExpireDate(null);
            }
        }

        $this->entityManager->flush();

        $output->writeln('LumpSums updated where expiry dates were reached.');
        return Command::SUCCESS;
    }
}
