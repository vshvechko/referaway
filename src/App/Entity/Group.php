<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="`group`")
 */
class Group extends AbstractEntity {
    use WithAuthoincrementId;

    const VISIBILITY_PUBLIC = 0;
    const VISIBILITY_PRIVATE = 1;
    const VISIBILITY_CLOSED = 2;

    public function __construct() {
        parent::__construct();
        $this->members = new ArrayCollection();
        $this->setVisibility(self::VISIBILITY_PUBLIC);
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
     * @Column(type="integer", length=2)
     * @var string
     */
    protected $visibility;

    /**
     * @Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $image;
    
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
            if ($member->getContact()->getUser() == $user) {
                return $member->getRole() == UserGroup::ROLE_ADMIN;
            }
        }
        return false;
    }

    public function getMembers() {
        return array_map(
            function (UserGroup $userGroup) {
                return $userGroup->getContact();
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

    /**
     * @return string
     */
    public function getVisibility() {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     * @return $this
     */
    public function setVisibility($visibility) {
        $this->visibility = $visibility;
        return $this;
    }

    public function getMemberStatus(User $user) {
        if ($this->getOwner() == $user) {
            return UserGroup::MEMBER_STATUS_MEMBER;
        }
        /**
         * @var UserGroup $userGroup
         */
        $userGroup = $this->findMemberByUser($user);
        if ($userGroup) {
            return $userGroup->getMemberStatus();
        }
        return null;
    }

    public function findMemberByUser(User $user) {
        /**
         * @var UserGroup $userGroup
         */
        foreach ($this->members as $userGroup) {
            if ($userGroup->getContact() && $userGroup->getContact()->getUser() == $user) {
                return $userGroup;
            }
        }
        return null;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    
}