<?php

namespace AlexGeno\PhoneVerificationBundle\Command;

use AlexGeno\PhoneVerification\Exception;
use AlexGeno\PhoneVerification\Manager\Initiator;
use AlexGeno\PhoneVerificationBundle\TranslatorTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'phone-verification:initiate',
    description: 'Sends a notification with an otp to a recipient',
)]
class PhoneVerificationInitiateCommand extends Command
{
    use TranslatorTrait;

    public function __construct(private Initiator $manager, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->translator = $translator;
    }

    protected function configure(): void
    {
        $this->addOption('to', null, InputOption::VALUE_REQUIRED, 'A recipient phone number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $return = Command::SUCCESS;
        try {
            $this->manager->initiate($input->getOption('to'));
            $io->success($this->trans('initiation_success'));
        } catch (Exception $e) {
            $return = Command::FAILURE;
            $io->error($e->getMessage());
        }

        return $return;
    }
}
