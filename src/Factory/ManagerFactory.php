<?php

namespace AlexGeno\PhoneVerificationBundle\Factory;

use AlexGeno\PhoneVerification\Manager\Completer;
use AlexGeno\PhoneVerification\Manager\Initiator;
use AlexGeno\PhoneVerification\Sender\I as ISender;
use AlexGeno\PhoneVerification\Storage\I as IStorage;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagerFactory
{
    public function __construct(
        private IStorage $storage,
        private TranslatorInterface $translator,
        private string $translatorDomain,
        /**
         * @var array<mixed> ['otp' => [...], 'rate_limits' => [...]]
         */
        private array $rawConfig,
    ) {
    }

    /**
     * @return array<mixed> ['otp' => [...], 'rate_limits' => [...]]
     */
    private function config(): array
    {
        $config = $this->rawConfig;

        $config['otp']['message'] = fn ($otp) => $this->translator->trans('otp', ['%code%' => $otp], $this->translatorDomain);
        $config['rate_limits']['initiate']['message'] = fn ($phone, $periodSecs, $count) => $this->translator->trans('initiation_rate_limit', ['%sms%' => $count, '%hours%' => $periodSecs / 60 / 60], $this->translatorDomain);
        $config['rate_limits']['complete']['message'] = fn ($phone, $periodSecs, $count) => $this->translator->trans('completion_rate_limit', ['%times%' => $count, '%minutes%' => $periodSecs / 60], $this->translatorDomain);
        $config['otp']['message_expired'] = fn ($periodSecs, $otp) => $this->translator->trans('expired', ['%minutes%' => $periodSecs / 60], $this->translatorDomain);
        $config['otp']['message_incorrect'] = fn ($otp) => $this->translator->trans('incorrect', [], $this->translatorDomain);

        return $config;
    }

    public function initiator(ISender $sender): Initiator
    {
        return (new \AlexGeno\PhoneVerification\Manager($this->storage, $this->config()))->sender($sender);
    }

    public function completer(): Completer
    {
        return new \AlexGeno\PhoneVerification\Manager($this->storage, $this->config());
    }
}
