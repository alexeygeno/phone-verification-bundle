<?php

namespace AlexGeno\PhoneVerificationBundle\Notifier;

use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientTrait;

class SmsRecipient implements SmsRecipientInterface
{
    use SmsRecipientTrait;

    /**
     * Set the phone number.
     */
    public function phone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
