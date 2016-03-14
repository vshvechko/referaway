<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * @Entity
 * @Table(name="contact")
 */
class Contact extends AbstractEntity
{
    use WithAuthoincrementId;

    const TYPE_VENDOR = 1;
    const TYPE_CUSTOMER = 2;

    public function __construct()
    {
        parent::__construct();
        $this->customFields = new ArrayCollection();
    }

    /**
     * @Column(name="first_name", type="string", length=32, nullable=true)
     * @var string
     */
    protected $firstName;
    /**
     * @Column(name="last_name", type="string", length=32, nullable=true)
     * @var string
     */
    protected $lastName;
    /**
     * @Column(type="string", length=32, nullable=false)
     * @var string
     */
    protected $email;
    /**
     * @Column(type="string", length=32, nullable=true)
     * @var string
     */
    protected $phone;

    /**
     * @Column(type="string", length=32, nullable=false)
     * @var int
     */
    protected $type;

    /**
     * @Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $business;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ContactCustomField", mappedBy="contact", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $customFields;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @param string $business
     * @return $this
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    public function getCustomFields() {
        return $this->customFields->toArray();
    }

    public function setCustomFields(array $customFields) {
        $this->customFields = new ArrayCollection();
        foreach ($customFields as $customField) {
            $this->customFields->add($customField);
        }
    }
}