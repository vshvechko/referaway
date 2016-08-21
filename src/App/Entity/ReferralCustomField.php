<?php

namespace App\Entity;


/**
 * Class ReferralCustomField
 * @package App\Entity
 *
 * @Entity
 * @Table(name="referral_custom_field")
 */
class ReferralCustomField extends AbstractCustomField
{
    use WithAuthoincrementId;
    
    public function __construct()
    {
        parent::__construct();
        $this->group = 0;
    }

    protected $notPopulatedFields = ['referral'];

    /**
     * @var Referral
     * @ManyToOne(targetEntity="Referral")
     * @JoinColumn(name="referral_id", referencedColumnName="id", nullable=false)
     */
    protected $referral;

    /**
     * @Column(name="`group`", type="integer", length=1, nullable=false, options={"default" : 0})
     * @var int
     */
    protected $group;

    /**
     * @return Referral
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * @param Referral $referral
     * @return $this
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param int $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    
}