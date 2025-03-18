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
    const API_HOST = 'http://127.0.0.1:8000/api/v1/';

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
        private HttpClientInterface $httpClient
    ) {}

    public function witnesses($force = false)
    {
        if (!$force && $this->isWitnessesSync) {
            return;
        }
        $this->roles($force);

        $this->witnesses = $this->witnessRepository->getAllWithIdKey();

        $url = self::API_HOST . 'witness';

        do {
            $response = $this->httpClient->request(
                'GET',
                $url
            );
            if ($response->getStatusCode() != 200) {
                dd('some errror');
            }
            $dataArray =  $response->toArray();

            foreach ($dataArray['data'] ?? [] as $item) {
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

            $url = $dataArray['links']['next'] ?? null;
        } while ($url);


        $this->disableAutoincreament(Witness::class);

        $this->entityManager->flush();

        $this->witnesses = $this->witnessRepository->getAllWithIdKey();
    }

    public function roles($force = false)
    {
        if (!$force && $this->isRolesSync) {
            return;
        }


        $this->roles = $this->roleRepository->getAllWithIdKey();

        $url = self::API_HOST . 'role';


        do {
            $response = $this->httpClient->request(
                'GET',
                $url
            );
            if ($response->getStatusCode() != 200) {
                dd('some errror');
            }
            $dataArray =  $response->toArray();

            foreach ($dataArray['data'] ?? [] as $item) {
                $role = $this->roles[$item['attributes']['id']] ?? new Role();
                $role->setId($item['attributes']['id']);
                $role->setName($item['attributes']['name']);
                $role->setPriority($item['attributes']['priority']);
                if (!isset($this->roles[$item['attributes']['id']])) {
                    $this->entityManager->persist($role);
                }
            }
            $url = $dataArray['links']['next'] ?? null;
        } while ($url);


        $this->disableAutoincreament(Role::class);

        $this->entityManager->flush();

        $this->roles = $this->roleRepository->getAllWithIdKey();
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


        $url = self::API_HOST . 'task-witness-date';

        do {
            $response = $this->httpClient->request(
                'GET',
                $url
            );
            if ($response->getStatusCode() != 200) {
                dd('some errror');
            }
            $dataArray =  $response->toArray();

            foreach ($dataArray['data'] ?? [] as $item) {
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

            $url = $dataArray['links']['next'] ?? null;
        } while ($url);


        $this->disableAutoincreament(TaskWitnessDate::class);

        $this->entityManager->flush();

        $this->taskWitnessDates = $this->taskWitnessDateRepository->getAllWithIdKey();
    }
}
