<?php

namespace AlexGeno\PhoneVerificationBundle\Command;

use AlexGeno\PhoneVerification\Exception;
use AlexGeno\PhoneVerification\Manager\Completer;
use AlexGeno\PhoneVerificationBundle\TranslatorTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'phone-verification:complete',
    description: 'Verifies a recipient',
)]
class PhoneVerificationCompleteCommand extends Command
{
    use TranslatorTrait;

    public function __construct(private Completer $manager, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->translator = $translator;
    }

    /**
     * Configure the console options.
     */
    protected function configure(): void
    {
        $this->addOption('to', null, InputOption::VALUE_REQUIRED, 'A recipient phone number')
             ->addOption('otp', null, InputOption::VALUE_REQUIRED, 'A one-time password');
    }

    /**
     * Execute the console command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $return = Command::SUCCESS;
        try {
            $this->manager->complete($input->getOption('to'), $input->getOption('otp'));
            $io->success($this->trans('completion_success'));
        } catch (Exception $e) {
            $return = Command::FAILURE;
            $io->error($e->getMessage());
        }

        return $return;
    }
}
