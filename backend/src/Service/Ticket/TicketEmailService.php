<?php

namespace App\Service\Ticket;

use App\Entity\Client;
use App\Entity\Ticket;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TicketEmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private TicketStorageService $storageService,
    ) {}

    public function sendTicketToClient(Client $client, Ticket $ticket, string $fromEmail): void
    {
        if (!$client->getEmail()) {
            throw new \DomainException('Le client n’a pas d’email.');
        }

        $absolutePath = $this->storageService->absolutePath($ticket->getStoragePath());

        if (!is_file($absolutePath)) {
            throw new \DomainException('Le fichier PDF du ticket est introuvable.');
        }

        $email = (new Email())
            ->from($fromEmail)
            ->to($client->getEmail())
            ->subject('Votre ticket de réparation')
            ->text("Bonjour,\n\nVeuillez trouver en pièce jointe votre ticket de réparation.\n")
            ->attachFromPath($absolutePath, $ticket->getFilename(), $ticket->getMimeType());

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new \DomainException('Envoi email impossible : ' . $e->getMessage());
        }
    }
}