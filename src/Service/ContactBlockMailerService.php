<?php

namespace Partitech\SonataExtra\Service;

use Partitech\SonataExtra\Entity\Contact;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ContactBlockMailerService
{
    public const string CONFIRMATION_TEMPLATE = '@PartitechSonataExtra/Blocks/contact/confirmation_email.html.twig';
    public const string ADMIN_NOTIFICATION_TEMPLATE = '@PartitechSonataExtra/Blocks/contact/admin_notification_email.html.twig';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function sendConfirmation(array $settings, Contact $contact): void
    {

        $email = $this->createEmail(
            to: $contact->getEmail(),
            subject: $settings['mailSubject'],
            template: (empty($settings['template_mail_confirmation'])) ? self::CONFIRMATION_TEMPLATE : $settings['template_mail_confirmation'],
            contact: $contact,
            settings: $settings
        );
        $this->sendEmail($email);
    }

    public function sendAdminConfirmation(array $settings, Contact $contact): void
    {
        $email = $this->createEmail(
            to: $settings['sendToAddress'],
            subject: $settings['mailSubject'],
            template: (empty($settings['template_mail_admin_notification'])) ? self::ADMIN_NOTIFICATION_TEMPLATE : $settings['template_mail_confirmation'],
            contact: $contact,
            settings: $settings
        );
        $this->sendEmail($email);
    }

    private function createEmail(string $to, string $subject, string $template, Contact $contact, array $settings): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->to(new Address($to))
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'formData' => $contact,
                'settings' => $settings,
            ]);
    }

    private function sendEmail(TemplatedEmail $email): void
    {
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Email sending failed: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
