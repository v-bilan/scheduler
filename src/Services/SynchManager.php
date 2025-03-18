<?php

namespace App\Services;

use App\Entity\Role;
use App\Entity\TaskWitnessDate;
use App\Entity\Witness;
use App\Repository\RoleRepository;
use App\Repository\TaskWitnessDateRepository;
use App\Repository\WitnessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Contracts\HttpClient\HttpClientInterface;;

class SynchManager
{


    private $isRolesSync = false;
    private $isTasksSync = false;
    private $isWitnessesSync = false;

    private array $roles;
    private array $witnesses;
    private array $taskWitnessDates;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoleRepository $roleRepository,
        private WitnessRepository $witnessRepository,
        private TaskWitnessDateRepository $taskWitnessDateRepository,
        private SyncApiClient $syncApiClient
    ) {}


    public function roles($force = false)
    {
        if (!$force && $this->isRolesSync) {
            return;
        }

        $this->roles = $this->roleRepository->getAllWithIdKey();

        $items = $this->syncApiClient->getItems('role');
        foreach ($items as $item) {
            $role = $this->roles[$item['attributes']['id']] ?? new Role();
            $role->setId($item['attributes']['id']);
            $role->setName($item['attributes']['name']);
            $role->setPriority($item['attributes']['priority']);
            if (!isset($this->roles[$item['attributes']['id']])) {
                $this->entityManager->persist($role);
            }
        }

        $this->disableAutoincreament(Role::class);

        $this->entityManager->flush();

        $this->roles = $this->roleRepository->getAllWithIdKey();
    }

    public function witnesses($force = false)
    {
        if (!$force && $this->isWitnessesSync) {
            return;
        }
        $this->roles($force);

        $this->witnesses = $this->witnessRepository->getAllWithIdKey();

        $items = $this->syncApiClient->getItems('witness');
        foreach ($items as $item) {
            $witness = $this->witnesses[$item['attributes']['id']] ?? new Witness();
            if (!$witness->getId()) {
                $witness->setId($item['attributes']['id']);
            }
            $witness->setFullName($item['attributes']['fullName']);
            $witness->setActive($item['attributes']['active']);
            if (!isset($this->witnesses[$item['attributes']['id']])) {
                $this->entityManager->persist($witness);
            }

            foreach ($item['includes'] as $include) {
                if ($include['type'] != 'role') {
                    continue;
                }
                $witness->addRole($this->roles[$include['id']]);
            }
        }




        $this->disableAutoincreament(Witness::class);

        $this->entityManager->flush();

        $this->witnesses = $this->witnessRepository->getAllWithIdKey();
    }

    private function disableAutoincreament($class)
    {
        $metadata = $this->entityManager->getClassMetaData($class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
    }


    public function tasks($force = false)
    {
        if (!$force && $this->isTasksSync) {
            return;
        }
        $this->witnesses($force);

        $this->taskWitnessDates = $this->taskWitnessDateRepository->getAllWithIdKey();

        $items = $this->syncApiClient->getItems('task-witness-date');
        foreach ($items as $item) {
            $taskWitnessDate = $this->taskWitnessDates[$item['attributes']['id']] ?? new TaskWitnessDate();

            if (!$taskWitnessDate->getId()) {
                $taskWitnessDate->setId((int)$item['attributes']['id']);
            }

            $taskWitnessDate->setTask($item['attributes']['task']);
            $taskWitnessDate->setDate(\DateTime::createFromFormat('Y-m-d', $item['attributes']['date']));
            if (!isset($this->taskWitnessDates[$item['attributes']['id']])) {
                $this->entityManager->persist($taskWitnessDate);
            }

            foreach ($item['includes'] as $include) {
                if ($include['type'] == 'role') {
                    $taskWitnessDate->setRole($this->roles[$include['id']]);
                }
                if ($include['type'] == 'witness') {
                    $taskWitnessDate->setWitness($this->witnesses[$include['id']]);
                }
            }
        }

        $this->disableAutoincreament(TaskWitnessDate::class);

        $this->entityManager->flush();

        $this->taskWitnessDates = $this->taskWitnessDateRepository->getAllWithIdKey();
    }
}
