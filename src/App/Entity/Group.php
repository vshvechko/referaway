<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="`group`")
 */
class Group extends AbstractEntity {

    public function __construct() {
        parent::__construct();
        $this->members = new ArrayCollection();
    }

    /**
     * @Column(type="string", length=32)
     * @var string
     */
    protected $name;

    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="User", mappedBy="groups")
     */
    protected $members;

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return User
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner) {
        $this->owner = $owner;
        $owner->addGroup($this);
    }

    public function getMembers() {
        return $this->members->toArray();
    }

    /**
     * @param User $user
     */
    public function addMember(User $user) {
        $this->members->add($user);
    }

    public function removeMember(User $user) {
        if ($this->owner == $user) {
            throw new \InvalidArgumentException('Cannot remove group owner');
        }
        $this->members->removeElement($user);
    }
}