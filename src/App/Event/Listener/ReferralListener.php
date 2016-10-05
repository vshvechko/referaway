<?php

namespace App\Event\Listener;


use App\Event\ReferralStatusChangedEvent;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;

class ReferralListener extends AbstractListener
{
    public function onStatusChanged(ReferralStatusChangedEvent $event) {
        $referral = $event->getReferral();
        $owner = $referral->getOwner();
        if ($device = $owner->getDevice()) {
            // send push notification
            $pushManager = $this->getContainer()->get('notificationManager');
            $factory = $this->getContainer()->get('notificationAdapter');
            $adapter = $factory($device->getType());

            $devices = new Device($device->getToken());
            $message = [
                'type' => 'referral',
                'action' => 'statusChanged',
                'id' => $referral->getId(),
                'name' => $referral->getName(),
                'value' => $referral->getStatus()
            ];
            if ($device->getType() == \App\Entity\Device::TYPE_APNS) {
                $message = new Message('Referral status changed', ['custom' => ['customData' => $message]]);
            } else {
                $message = new Message(json_encode($message));
            }

            $push = new Push($adapter, $devices, $message);
            $pushManager->add($push);
            $pushManager->push();
        }
    }
}
