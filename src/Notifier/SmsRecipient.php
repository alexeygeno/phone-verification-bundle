<?php

namespace AlexGeno\PhoneVerificationBundle\Notifier;

use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientTrait;

class SmsRecipient implements SmsRecipientInterface
{
    use SmsRecipientTrait;

    /**
     * Sets the phone number (no spaces, international code like in +3312345678).
     *
     * @return $this
     */
    public function phone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
