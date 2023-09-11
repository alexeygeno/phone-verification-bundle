<?php

namespace AlexGeno\PhoneVerificationBundle\Command;

use AlexGeno\PhoneVerification\Manager\Initiator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use AlexGeno\PhoneVerification\Exception;

#[AsCommand(
    name: 'phone-verification:complete',
    description: 'Verifies a recipient',
)]
class PhoneVerificationCompleteCommand extends Command
{
    public function __construct(private Initiator $manager, private TranslatorInterface $translator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('to', null, InputOption::VALUE_REQUIRED, 'A recipient phone number')
             ->addOption('otp', null, InputOption::VALUE_REQUIRED, 'A one-time password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $return = Command::SUCCESS;
        try {
            $this->manager->complete($input->getOption('to'), $input->getOption('otp'));
            $io->success($this->translator->trans('completion_success', [], 'alex_geno_phone_verification'));
        }catch(Exception $e){
            $return = Command::FAILURE;
            $io->error($e->getMessage());
        }

        return $return;
    }
}
