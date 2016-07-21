<?php

namespace App\DAO;


use Doctrine\ORM\Query;

class CategoryDAO extends AbstractDAO {

    protected function getRepositoryName() {
        return 'App\Entity\Category';
    }

    public function loadTree($hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c, s, ss')
            ->from($this->getRepositoryName(), 'c')
            ->leftJoin('c.children', 's')
            ->leftJoin('s.children', 'ss')
            ->where($qb->expr()->isNull('c.parent'));

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }
}