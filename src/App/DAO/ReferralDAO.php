<?php

namespace App\DAO;

use App\Entity\Referral;
use App\Entity\ReferralCustomField;
use App\Entity\ReferralImage;
use App\Entity\User;
use Doctrine\ORM\Query;

class ReferralDAO extends AbstractDAO
{
    protected function getRepositoryName()
    {
        return 'App\Entity\Referral';
    }

    protected function getImageRepositoryName()
    {
        return 'App\Entity\ReferralImage';
    }

    public function createReferral($data, $owner, $target, $customFields = null) {
        $entity = new Referral();
        $entity->populate($data);
        $entity->setOwner($owner)
            ->setTarget($target);

        $this->save($entity);

        // set custom fields
        $fields = [];
        if (is_array($customFields)) {
            foreach ($customFields as $fieldData) {
                $field = new ReferralCustomField();
                $field->populate($fieldData);
                $field->setReferral($entity);
                $fields[] = $field;
            }
            $entity->setCustomFields($fields);
        }

        $this->save($entity, false);

        $this->getEntityManager()->flush();

        return $entity;
    }

    public function updateReferral(Referral $entity, $data, $owner, $target, $customFields = null) {
        $entity->populate($data);
        $entity->setOwner($owner);
        if (!is_null($target)) {
            $entity->setTarget($target);
        }
        if (isset($data['isRead'])) {
            $entity->setIsRead($data['isRead'] ? 1 : 0);
        }

        // set custom fields
        if (is_array($customFields)) {
            $fields = [];
            foreach ($customFields as $fieldData) {
                $field = new ReferralCustomField();
                $field->populate($fieldData);
                $field->setReferral($entity);
                $fields[] = $field;
            }
            $entity->setCustomFields($fields);
        }

        $this->save($entity);

        return $entity;
    }

    /**
     * @param User $user
     * @param null $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getFindReferalsQB(User $user, $search = null) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('r, u, c, i, cf, o')
            ->from($this->getRepositoryName(), 'r')
            ->leftJoin('r.owner', 'o')
            ->leftJoin('r.target', 'c')
            ->leftJoin('c.user', 'u')
            ->leftJoin('r.images', 'i')
            ->leftJoin('r.customFields', 'cf');
        if ($search) {
            $qb->andWhere(
                $qb->expr()->like('LOWER(r.name)', ':s')
            )->setParameter('s', '%' . strtolower($search) . '%');
        }
        
        return $qb;
    }

    public function findSentReferralsByUser(User $user, $search = null, $hydrate = false, $skipCache = false) {
        $qb = $this->getFindReferalsQB($user, $search);
        $qb->andWhere(
            $qb->expr()->eq('r.owner', ':user')
        )->setParameter('user', $user);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }
    
    public function findReceivedReferralsByUser(User $user, $search = null, $hydrate = false, $skipCache = false) {
        $qb = $this->getFindReferalsQB($user, $search);
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->eq('c.user', ':user'),
                $qb->expr()->andX(
                    $qb->expr()->eq('r.owner', ':user'),
                    $qb->expr()->eq('r.type', ':type')
                )
            )
        )->andWhere($qb->expr()->neq('r.isRead', 1))
            ->setParameter('user', $user)
            ->setParameter('type', Referral::TYPE_SELF);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }
    
    /**
     * Search referrals related to user
     * @var $relationStatus null|'received'|'sent'
     * 
     * @inheritdoc
     */
    public function findReferralsByUser(User $user, $search = null, $relationStatus = null, $hydrate = false, $skipCache = false) {
        $qb = $this->getFindReferalsQB($user, $search);
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->eq('r.owner', ':user'),
                $qb->expr()->eq('c.user', ':user')
            )
        )->setParameter('user', $user);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @param bool $skipCache
     * @return ReferralImage
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findImageById($id, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getImageRepositoryName(), 'e')
            ->where($qb->expr()->eq('e.id', ':id'))->setParameter('id', $id);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }
}