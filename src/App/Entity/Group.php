<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="`group`")
 */
class Group extends AbstractEntity {
    use WithAuthoincrementId;

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
     * @OneToMany(targetEntity="UserGroup", mappedBy="group", cascade={"persist", "remove"}, orphanRemoval=true)
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
    }

    public function canAdmin(User $user) {
        if ($this->getOwner() == $user)
            return true;

        /**
         * @var UserGroup $member
         */
        foreach ($this->getUserGroups() as $member) {
            if ($member->getUser() == $user) {
                return $member->getRole() == UserGroup::ROLE_ADMIN;
            }
        }
        return false;
    }

    public function getMembers() {
        return array_map(
            function (UserGroup $userGroup) {
                return $userGroup->getUser();
            },
            $this->members->toArray()
        );
    }

    public function addUserGroup(UserGroup $userGroup) {

        if ($this->members->contains($userGroup)) {
            return;
        }
        $this->members->add($userGroup);
    }

    public function removeUserGroup(UserGroup $group) {
        $this->members->removeElement($group);
    }

    public function getUserGroups() {
        return $this->members->toArray();
    }
}