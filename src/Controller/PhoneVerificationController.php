<?php

namespace AlexGeno\PhoneVerificationBundle\Controller;

use AlexGeno\PhoneVerification\Exception;
use AlexGeno\PhoneVerification\Manager\Completer;
use AlexGeno\PhoneVerification\Manager\Initiator;
use AlexGeno\PhoneVerificationBundle\TranslatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhoneVerificationController extends AbstractController
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function initiate(string $to, Initiator $manager): JsonResponse
    {
        $response = ['ok' => true,  'message' => $this->trans('initiation_success')];
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
        $response = ['ok' => true, 'message' => $this->trans('completion_success')];
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
