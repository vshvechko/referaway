<?php

namespace App\DAO;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

abstract class AbstractDAO {
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->setEntityManager($entityManager);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager() {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $entity
     * @param bool $flush
     * @return $this
     */
    public function save($entity, $flush = true) {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->flush();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function flush() {
        $this->getEntityManager()->flush();

        return $this;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function detach($entity) {
        $this->getEntityManager()->detach($entity);

        return $this;
    }

    /**
     * @param $entity
     * @param bool $flush
     * @return $this
     */
    public function remove($entity, $flush = true) {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->flush();
        }

        return $this;
    }

    /**
     * @param bool $hydrate
     * @param bool $skipCache
     * @return array
     */
    public function findAll($hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getRepositoryName(), 'e');

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @param bool $skipCache
     * @return mixed
     */
    public function findById($id, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getRepositoryName(), 'e')
            ->where($qb->expr()->eq('e.id', ':id'))->setParameter('id', $id);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }


    /**
     * @param bool $skipCache
     * @return mixed
     */
    public function count($skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(e.id)')
            ->from($this->getRepositoryName(), 'e');

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getSingleScalarResult();
    }

    abstract protected function getRepositoryName();
}