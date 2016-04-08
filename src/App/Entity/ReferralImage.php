<?php

namespace App\Entity;

/**
 * @Entity
 * @Table(name="referral_image")
 */
class ReferralImage extends AbstractEntity
{
    use WithAuthoincrementId;

    protected $notPopulatedFields = ['referral'];
    
    /**
     * @Column(type="string", length=255, nullable=false)
     */
    protected $image;

    /**
     * @var Referral
     * @ManyToOne(targetEntity="Referral")
     * @JoinColumn(name="referral_id", referencedColumnName="id", nullable=false)
     */
    protected $referral;

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