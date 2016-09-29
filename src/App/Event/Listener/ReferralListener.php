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
            $adapter = ($this->getContainer()->get('notificationAdapter'))($device->getType());

            $devices = new Device($device->getToken());
            $message = [
                'type' => 'referral',
                'action' => 'statusChanged',
                'id' => $referral->getId(),
                'name' => $referral->getName(),
                'value' => $referral->getStatus()
            ];
            $message = new Message(json_encode($message));

            $push = new Push($adapter, $devices, $message);
            $pushManager->add($push);
            $pushManager->push();
        }
    }
}
