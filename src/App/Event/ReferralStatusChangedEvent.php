<?php

namespace App\Event;


use App\Entity\Referral;
use Symfony\Component\EventDispatcher\Event;

class ReferralStatusChangedEvent extends Event
{
    const NAME = 'referral.status.changed';

    /**
     * @var Referral
     */
    protected $referral;

    public function __construct(Referral $referral)
    {
        $this->referral = $referral;
    }

    /**
     * @return Referral
     */
    public function getReferral()
    {
        return $this->referral;
    }

}