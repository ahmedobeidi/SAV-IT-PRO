<?php

namespace App\Service\Ticket;

use App\Entity\Client;
use App\Entity\Ticket;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TicketEmailService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendTicketToClient(Client $client, Ticket $ticket, string $fromEmail): void
    {
        if (!$client->getEmail()) {
            throw new \DomainException('Le client n’a pas d’email.');
        }

        $email = (new Email())
            ->from($fromEmail)
            ->to($client->getEmail())
            ->subject('Votre ticket de réparation')
            ->text("Bonjour,\n\nVeuillez trouver en pièce jointe votre ticket de réparation.\n")
            ->attach($ticket->getContent(), $ticket->getFilename(), $ticket->getMimeType());

        $this->mailer->send($email);
    }
}