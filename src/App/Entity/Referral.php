<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Referral
 * @package App\Entity
 *
 * @Entity
 * @Table(name="referral")
 */
class Referral extends AbstractEntity
{
    use WithAuthoincrementId;
    use WithCreateDate;

    const TYPE_CUSTOMER = 1;
    const TYPE_VENDOR = 2;
    const TYPE_SELF = 3;

    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_FAILED = 2;

    protected $notPopulatedFields = ['owner', 'images', 'customFields', 'created'];

    /**
     * @var Contact
     * @ManyToOne(targetEntity="Contact")
     * @JoinColumn(name="contact_id", referencedColumnName="id", nullable=true)
     */
    protected $target;
    /**
     * @Column(name="name", type="string", length=255)
     * @var string
     */
    protected $name;
    /**
     * @Column(name="note", type="text", nullable=true)
     * @var string
     */
    protected $note;

    /**
     * @Column(name="note1", type="text", nullable=true)
     * @var string
     */
    protected $note1;
    /**
     * @Column(type="integer", length=1, nullable=false)
     * @var int
     */
    protected $type;
    /**
     * @Column(type="integer", length=1, nullable=false)
     * @var int
     */
    protected $status;
    
    // TODO
    protected $revenue;

    /**
     * @Column(name="date_completed", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $dateCompleted;
    /**
     * @var User
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     */
    protected $owner;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ReferralCustomField", mappedBy="referral", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $customFields;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ReferralImage", mappedBy="referral", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $images;

    /**
     * @Column(name="is_read", type="integer", length=1, nullable=false)
     * @var int
     */
    protected $isRead;


    public function __construct()
    {
        parent::__construct();
        $this->customFields = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->setStatus(self::STATUS_PENDING);
        $this->setIsRead(0);
        $this->setCreated(new \DateTime('now'));
    }

    public function getCustomFields($type = null) {
        if (is_null($type)) {
            return $this->customFields->toArray();
        } else {
            switch ($type) {
                case ReferralCustomField::TYPE_EMAIL:
                case ReferralCustomField::TYPE_PHONE:
                case ReferralCustomField::TYPE_ADDRESS:
                    $fields = [];
                    /**
                     * @var ReferralCustomField $customField
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
        return $this;
    }

    public function getEmailCustomFields() {
        return $this->getCustomFields(ReferralCustomField::TYPE_EMAIL);
    }

    /**
     * @return Contact
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param Contact $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
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
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        if ($this->status == self::STATUS_COMPLETED) {
            $this->setDateCompleted(new \DateTime('now'));
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRevenue()
    {
        return $this->revenue;
    }

    /**
     * @param mixed $revenue
     * @return $this
     */
    public function setRevenue($revenue)
    {
        $this->revenue = $revenue;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCompleted()
    {
        return $this->dateCompleted;
    }

    /**
     * @param \DateTime $dateCompleted
     * @return $this
     */
    public function setDateCompleted($dateCompleted)
    {
        $this->dateCompleted = $dateCompleted;
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

    public function getImages()
    {
        return $this->images->toArray();
    }

    public function setImages(array $images) {
        $this->images->clear();
        foreach ($images as $image) {
            $this->addImage($image);
        }
        return $this;
    }

    public function addImage(ReferralImage $image) {
        $this->images->add($image);
        $image->setReferral($this);
        return $this;
    }

    public function canAdmin(User $user) {
        if ($this->getOwner() == $user)
            return true;
        
        if ($this->getTarget() && $this->getTarget()->getUser() == $user)
            return true;
        
        return false;
    }

    /**
     * @return int
     */
    public function getIsRead() {
        return $this->isRead;
    }

    /**
     * @param int $isRead
     * @return int
     */
    public function setIsRead($isRead) {
        $this->isRead = $isRead;
        return $this->isRead;
    }

    /**
     * @return string
     */
    public function getNote1() {
        return $this->note1;
    }

    /**
     * @param string $note1
     * @return $this
     */
    public function setNote1($note1) {
        $this->note1 = $note1;
        return $this;
    }


}