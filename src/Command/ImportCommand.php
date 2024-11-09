<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Witness;
use App\Repository\RoleRepository;
use App\Repository\WitnessRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-csv',
    description: 'Add a short description for your command',
)]
class ImportCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoleRepository $roleRepository,
        private WitnessRepository $witnessRepository
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Imports data from a CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'The CSV file to import');
    }

    protected function updateRoles($header) : array
    {
        $roles = $this->roleRepository->findAll();
        $roleSet = [];
        foreach ($roles as $role) {
            $roleSet[$role->getName()] = $role;
        }
        foreach ($header as $roleName) {
            if (trim($roleName) == 'full_name' || isset($roleSet[$roleName]))  continue;
            $role = new Role();
            $role->setName($roleName);
            $this->entityManager->persist($role);
            $this->entityManager->flush();
            $roleSet[$role->getName()] = $role;
        }
        return $roleSet;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath)) {
            $io->error("File $filePath does not exist.");
            return Command::FAILURE;
        }

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0); // Пропустіть заголовок CSV
        } catch (\Exception $e) {
            $io->error("Could not read the file: " . $e->getMessage());
            return Command::FAILURE;
        }

        $roles = $this->updateRoles($csv->getHeader());
        $records = $csv->getRecords();
        foreach ($records as $record) {

            if (!isset($record['full_name'])) continue;
            $fullName = $this->prepareString($record['full_name']);
            $witness = $this->witnessRepository->findOneBy(['fullName' => $fullName]);
            if (!$witness) {
                $witness = new Witness();
                $witness->setFullName($fullName);
                $this->entityManager->persist($witness);
            }
            foreach ($record as $key=> $role) {

                if ($key == 'full_name' || !isset($roles[$key]) || !$role)  continue;
                $witness->addRole($roles[$key]);
            }
        }
       // $header = $csv->getHeader();
       // print_r($header);
          $this->entityManager->flush();

        $io->success('Data successfully imported from CSV file.');

        return Command::SUCCESS;
    }
    private function prepareString($fullName)
    {
        $fullName = preg_replace('!\s+!', ' ', $fullName);
        return $this->mb_ucwords($fullName);
    }
    function mb_ucwords($string, $encoding = 'UTF-8') {
        // Розбиваємо рядок на слова
        $words = mb_split('\s', $string);

        // Перетворюємо першу літеру кожного слова на велику
        $words = array_map(function($word) use ($encoding) {
            return mb_strtoupper(mb_substr($word, 0, 1, $encoding), $encoding) .
                mb_substr($word, 1, mb_strlen($word, $encoding) - 1, $encoding);
        }, $words);

        // Об'єднуємо слова назад у рядок
        return implode(' ', $words);
    }
}