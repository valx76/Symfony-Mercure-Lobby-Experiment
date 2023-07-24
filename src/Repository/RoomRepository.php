<?php

namespace App\Repository;

use App\Entity\Room;
use App\Exception\RoomNotFoundException;
use App\Exception\UserAlreadyInRoomException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 *
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function save(Room $room): int
    {
        $this->getEntityManager()->persist($room);
        $this->getEntityManager()->flush();

        return $room->getId();
    }

    /**
     * @throws RoomNotFoundException
     */
    public function findById(int $id): Room
    {
        $room = $this->findOneBy(['id' => $id]);

        if ($room === null) {
            throw new RoomNotFoundException(
                sprintf('The room %d has not been found.', $id)
            );
        }

        return $room;
    }
}
