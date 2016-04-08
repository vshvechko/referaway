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

    protected $notPopulatedFields = ['referral'];

    /**
     * @var Referral
     * @ManyToOne(targetEntity="Referral")
     * @JoinColumn(name="referral_id", referencedColumnName="id", nullable=false)
     */
    protected $referral;

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


}