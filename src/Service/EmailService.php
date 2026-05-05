<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\RendezVous;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly string $fromEmail,
        private readonly string $fromName,
    ) {}

    public function sendRdvConfirmation(RendezVous $rdv): void
    {
        $html = $this->twig->render('emails/rdv_confirmation.html.twig', ['rdv' => $rdv]);

        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($rdv->getClient()->getEmail(), $rdv->getClient()->getFullName()))
            ->subject('Votre rendez-vous BeautyAffair est confirmé')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendRdvStatutChange(RendezVous $rdv): void
    {
        $statuts = [
            RendezVous::STATUT_CONFIRME => 'confirmé ✓',
            RendezVous::STATUT_ANNULE   => 'annulé',
            RendezVous::STATUT_TERMINE  => 'terminé',
        ];

        $html = $this->twig->render('emails/rdv_statut.html.twig', ['rdv' => $rdv]);

        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($rdv->getClient()->getEmail(), $rdv->getClient()->getFullName()))
            ->subject('Mise à jour de votre rendez-vous — ' . ($statuts[$rdv->getStatut()] ?? $rdv->getStatut()))
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendCommandeConfirmation(Commande $commande): void
    {
        $html = $this->twig->render('emails/commande_confirmation.html.twig', ['commande' => $commande]);

        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($commande->getClient()->getEmail(), $commande->getClient()->getFullName()))
            ->subject('Commande #' . $commande->getId() . ' confirmée — BeautyAffair')
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendContactMessage(string $nomComplet, string $emailClient, string $sujet, string $message, ?string $telephone = null): void
    {
        $html = $this->twig->render('emails/contact.html.twig', [
            'nom'       => $nomComplet,
            'email'     => $emailClient,
            'sujet'     => $sujet,
            'message'   => $message,
            'telephone' => $telephone,
        ]);

        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($this->fromEmail)
            ->replyTo(new Address($emailClient, $nomComplet))
            ->subject('[BeautyAffair] ' . $sujet . ' — ' . $nomComplet)
            ->html($html);

        $this->mailer->send($email);
    }
}
