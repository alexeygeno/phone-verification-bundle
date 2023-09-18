<?php

namespace AlexGeno\PhoneVerificationBundle;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorTrait
{
    protected TranslatorInterface $translator;

    /**
     * @param array<mixed> $parameters
     */
    protected function trans(string $id, array $parameters = [], string $domain = 'alex_geno_phone_verification', string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
