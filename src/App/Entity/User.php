<?php

namespace App\Entity;

use App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping;

/**
 * @Entity
 * @Table(name="user")
 */
class User extends AbstractEntity {
    use WithAuthoincrementId;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public function __construct() {
        parent::__construct();
        $this->groups = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->setIsActive(self::STATUS_ACTIVE);
    }

    /**
     * @Column(type="string", length=255)
     * @var string
     */
    protected $email;

    /**
     * @Column(type="string", length=255)
     * @var string
     */
    protected $password;

    /**
     * @Column(name="first_name", type="string", length=32)
     * @var string
     */
    protected $firstName;

    /**
     * @Column(name="last_name", type="string", length=32)
     * @var string
     */
    protected $lastName;

    /**
     * @Column(type="string", length=32)
     * @var string
     */
    protected $phone;

    /**
     * @Column(type="string", length=32, nullable=true)
     * @var string
     */
    protected $business;

    /**
     * @Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $address;

    /**
     * @Column(type="string", length=32, nullable=true)
     * @var string
     */
    protected $city;

    /**
     * @Column(type="string", length=32, nullable=true)
     * @var string
     */
    protected $country;

    /**
     * @Column(type="string", length=6, nullable=true)
     * @var string
     */
    protected $zip;

    /**
     * @Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $token;

    /**
     * @Column(name="is_active", type="integer", length=1, nullable=false)
     * @var int
     */
    protected $isActive;

    /**
     * @Column(name="activation_code", type="string", length=40, nullable=true)
     * @var string
     */
    protected $activationCode;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="UserGroup", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $groups;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Contact", mappedBy="owner", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $contacts;

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone) {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getBusiness() {
        return $this->business;
    }

    /**
     * @param string $business
     */
    public function setBusiness($business) {
        $this->business = $business;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city) {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country) {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getZip() {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip) {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * @param int $isActive
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }

    /**
     * @return mixed
     */
    public function getActivationCode() {
        return $this->activationCode;
    }

    /**
     * @param mixed $activationCode
     */
    public function setActivationCode($activationCode) {
        $this->activationCode = $activationCode;
    }

    /**
     * @param string $token
     */
    public function setToken($token) {
        $this->token = $token;
    }

    public function getGroups() {
        return array_map(
            function (UserGroup $userGroup) {
                return $userGroup->getGroup();
            },
            $this->groups->toArray()
        );
    }

    public function addUserGroup(UserGroup $userGroup) {
        if ($this->groups->contains($userGroup)) {
            return;
        }
        $this->groups->add($userGroup);
    }

    public function removeUserGroup(UserGroup $group) {
        $this->groups->removeElement($group);
    }

    public function getContacts() {
        return $this->contacts->toArray();
    }

    public function addContact(Contact $contact) {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }
        return $this;
    }

    public function removeContact(Contact $contact) {
        $this->contacts->removeElement($contact);
        return $this;
    }
}