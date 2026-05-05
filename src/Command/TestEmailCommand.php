<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:test-email', description: 'Teste l\'envoi d\'email')]
class TestEmailCommand extends Command
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $to = 'dianengameni8@gmail.com';
        $output->writeln("Envoi d'un email de test à : <info>$to</info>");

        try {
            $email = (new Email())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($to)
                ->subject('Test email BeautyAffair')
                ->html('<p>Si tu reçois cet email, la configuration SMTP fonctionne !</p>');

            $this->mailer->send($email);
            $output->writeln('<info>Email envoyé avec succès !</info>');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
