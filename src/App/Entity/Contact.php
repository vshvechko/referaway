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

    protected $notPopulatedFields = ['user', 'owner', 'customFields', 'image'];

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
//    /**
//     * @Column(type="string", length=32, nullable=false)
//     * @var string
//     */
//    protected $email;
//    /**
//     * @Column(type="string", length=32, nullable=true)
//     * @var string
//     */
//    protected $phone;

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
     * @Column(type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ContactCustomField", mappedBy="contact", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $customFields;

    /**
     * Associated user
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * User owner
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    public function __construct()
    {
        parent::__construct();
        $this->customFields = new ArrayCollection();
    }

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

//    /**
//     * @return string
//     */
//    public function getEmail()
//    {
//        return $this->email;
//    }

//    /**
//     * @param string $email
//     * @return $this
//     */
//    public function setEmail($email)
//    {
//        $this->email = $email;
//        return $this;
//    }

//    /**
//     * @return string
//     */
//    public function getPhone()
//    {
//        return $this->phone;
//    }

//    /**
//     * @param string $phone
//     * @return $this
//     */
//    public function setPhone($phone)
//    {
//        $this->phone = $phone;
//        return $this;
//    }

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
     * @param User $owner
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    public function getCustomFields($type = null) {
        if (is_null($type)) {
            return $this->customFields->toArray();
        } else {
            switch ($type) {
                case ContactCustomField::TYPE_EMAIL:
                case ContactCustomField::TYPE_PHONE:
                case ContactCustomField::TYPE_ADDRESS:
                    $fields = [];
                    /**
                     * @var ContactCustomField $customField
                     */
                    foreach ($this->customFields as $customField) {
                        if ($customField->getType() == $type) {
                            $fields[] = $customField;
                        }
                    }
                    return $fields;
                    break;
                default:
                    throw new \InvalidArgumentException('Field type not supported');
            }
        }
    }

    public function setCustomFields(array $customFields) {
        $this->customFields->clear();
        foreach ($customFields as $customField) {
            $this->customFields->add($customField);
        }
    }

    public function getEmailCustomFields() {
        return $this->getCustomFields(ContactCustomField::TYPE_EMAIL);
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
    
    
}