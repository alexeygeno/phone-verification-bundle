<?php

namespace AlexGeno\PhoneVerificationBundle\Controller;

use AlexGeno\PhoneVerification\Exception;
use AlexGeno\PhoneVerification\Manager\Completer;
use AlexGeno\PhoneVerification\Manager\Initiator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhoneVerificationController extends AbstractController
{
    public function __construct(protected TranslatorInterface $translator)
    {
    }

    public function initiate(string $to, Initiator $manager): JsonResponse
    {
        $response = ['ok' => true,  'message' => $this->translator->trans('initiation_success', [], 'alex_geno_phone_verification')];
        $status = 200;
        try {
            $manager->initiate($to);
        } catch (Exception $e) {
            $response = ['ok' => false, 'message' => $e->getMessage()];
            $status = 406;
        }

        return $this->json($response, $status);
    }

    public function complete(string $to, int $otp, Completer $manager): JsonResponse
    {
        $response = ['ok' => true, 'message' => $this->translator->trans('completion_success', [], 'alex_geno_phone_verification')];
        $status = 200;
        try {
            $manager->complete($to, $otp);
        } catch (Exception $e) {
            $response = ['ok' => false, 'message' => $e->getMessage()];
            $status = 406;
        }

        return $this->json($response, $status);
    }
}
