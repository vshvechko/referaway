<?php

namespace App\Entity;


/**
 * @Entity
 * @Table(name="user_group")
 */
class UserGroup extends AbstractEntity {

    const ROLE_ADMIN = 1;
    const ROLE_MEMBER = 0;

    /**
     * @var User
     * @Id
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;
    /**
     * @var Group
     * @Id
     * @ManyToOne(targetEntity="Group")
     * @JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     */
    protected $group;

    /**
     * @var int
     * @Column(type="integer", length=1, nullable=false)
     */
    protected $role;

    public function __construct($user, $group, $role = self::ROLE_MEMBER) {
        parent::__construct();

        $this->setUser($user);
        $this->setGroup($group);
        $this->setRole($role);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        $user->addUserGroup($this);
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
        $group->addUserGroup($this);
    }

    /**
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param int $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }


}