<?php

namespace AlexGeno\PhoneVerificationBundle\Sender;

use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientTrait;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jan Schädlich <jan.schaedlich@sensiolabs.de>
 */
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
