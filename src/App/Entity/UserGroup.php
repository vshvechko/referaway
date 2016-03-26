<?php

namespace App\Entity;


/**
 * @Entity
 * @Table(name="user_group")
 */
class UserGroup extends AbstractEntity {
    use WithAuthoincrementId;

    const ROLE_ADMIN = 1;
    const ROLE_MEMBER = 0;

    const MEMBER_STATUS_PENDING = 0;
    const MEMBER_STATUS_REJECTED = 1;
    const MEMBER_STATUS_MEMBER = 2;

//    /**
//     * @var User
//     * @ManyToOne(targetEntity="User")
//     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
//     */
//    protected $user;

    /**
     * @var Contact
     * @ManyToOne(targetEntity="Contact")
     * @JoinColumn(name="contact_id", referencedColumnName="id", nullable=false)
     */
    protected $contact;

    /**
     * @var Group
     * @ManyToOne(targetEntity="Group", inversedBy="members")
     * @JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     */
    protected $group;

    /**
     * @var int
     * @Column(type="integer", length=1, nullable=false)
     */
    protected $role;

    /**
     * @var int
     * @Column(name="member_status", type="integer", length=1, nullable=false)
     */
    protected $memberStatus;

    public function __construct() {
        parent::__construct();

        $this->memberStatus = self::MEMBER_STATUS_PENDING;
        $this->setRole(self::ROLE_MEMBER);
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     * @return $this
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
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
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        $group->addUserGroup($this);
        return $this;
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
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return int
     */
    public function getMemberStatus() {
        return $this->memberStatus;
    }

    /**
     * @param int $memberStatus
     * @return $this
     */
    public function setMemberStatus($memberStatus) {
        $this->memberStatus = $memberStatus;
        return $this;
    }

}