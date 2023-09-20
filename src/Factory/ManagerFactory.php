<?php

namespace AlexGeno\PhoneVerificationBundle\Factory;

use AlexGeno\PhoneVerification\Manager\Completer;
use AlexGeno\PhoneVerification\Manager\Initiator;
use AlexGeno\PhoneVerification\Sender\I as ISender;
use AlexGeno\PhoneVerification\Storage\I as IStorage;
use AlexGeno\PhoneVerificationBundle\TranslatorTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagerFactory
{
    use TranslatorTrait;

    public function __construct(
        private IStorage $storage,
        TranslatorInterface $translator,
        /**
         * @var array<mixed> ['otp' => [...], 'rate_limits' => [...]]
         */
        private array $extensionConfig,
    ) {
        $this->translator = $translator;
    }

    /**
     * Compute the Manager config from the extension config.
     *
     * @return array<mixed> ['otp' => [...], 'rate_limits' => [...]]
     */
    protected function config(): array
    {
        $config = $this->extensionConfig;

        $config['otp']['message'] = fn ($otp) => $this->trans('otp', ['%code%' => $otp]);
        $config['rate_limits']['initiate']['message'] = fn ($phone, $periodSecs, $count) => $this->trans('initiation_rate_limit', ['%sms%' => $count, '%hours%' => $periodSecs / 60 / 60]);
        $config['rate_limits']['complete']['message'] = fn ($phone, $periodSecs, $count) => $this->trans('completion_rate_limit', ['%times%' => $count, '%minutes%' => $periodSecs / 60]);
        $config['otp']['message_expired'] = fn ($periodSecs, $otp) => $this->trans('expired', ['%minutes%' => $periodSecs / 60]);
        $config['otp']['message_incorrect'] = fn ($otp) => $this->trans('incorrect', []);

        return $config;
    }

    /**
     * Get the Initiator instance.
     */
    public function initiator(ISender $sender): Initiator
    {
        return (new \AlexGeno\PhoneVerification\Manager($this->storage, $this->config()))->sender($sender);
    }

    /**
     * Get the Completer instance.
     */
    public function completer(): Completer
    {
        return new \AlexGeno\PhoneVerification\Manager($this->storage, $this->config());
    }
}
