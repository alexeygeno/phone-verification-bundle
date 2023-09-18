<?php

namespace AlexGeno\PhoneVerificationBundle;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Help to avoid passing $domain to the 'trans' method every time you need a translation inside the bundle
 */
trait TranslatorTrait
{
    protected TranslatorInterface $translator;

    /**
     * Wrap TranslatorInterface::trans with 'alex_geno_phone_verification' as default domain
     *
     * @param array<mixed> $parameters
     */
    protected function trans(string $id, array $parameters = [], string $domain = 'alex_geno_phone_verification', string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
