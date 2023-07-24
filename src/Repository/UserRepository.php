<?php

namespace App\Repository;

use App\Entity\User;
use App\Exception\UserNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user): int
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user->getId();
    }

    /**
     * @throws UserNotFoundException
     */
    public function findById(int $id): User
    {
        $user = $this->findOneBy(['id' => $id]);

        if ($user === null) {
            throw new UserNotFoundException(
                sprintf('The user %d has not been found.', $id)
            );
        }

        return $user;
    }
}
