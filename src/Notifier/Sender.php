<?php

namespace AlexGeno\PhoneVerificationBundle\Notifier;

use AlexGeno\PhoneVerification\Sender\I;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Channel\SmsChannel;
use AlexGeno\PhoneVerificationBundle\Notifier\SmsRecipient;

class Sender implements I
{
    public function __construct(
        protected SmsChannel $smsChannel,
        protected Notification $notification,
        protected SmsRecipient $smsRecipient,
        protected string $transport
    )
    {

    }
    public function invoke(string $to, string $text)
    {
        $this->smsChannel->notify($this->notification->subject($text), $this->smsRecipient->phone($to), $this->transport);
    }

}

