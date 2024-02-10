<?php

namespace App\EventListener;

use App\Service\Mailjet;
use Brevo\Client\ApiException;
use App\Entity\ComposterContact;

class ComposterContactListener
{
    /**
     * @var Mailjet
     */
    private Mailjet $email;

    public function __construct(Mailjet $email)
    {
        $this->email = $email;
    }

    /**
     * @param ComposterContact $composterContact
     * @throws ApiException
     */
    public function prePersist(ComposterContact $composterContact): void
    {
        $composter = $composterContact->getComposter();
        $name = $composter->getName();
        $mc = $composter->getMc();

        $recipients = [];

        // Plus tous les référents qui sont ok pour être destinataires
        $notify_mc = true;
        $firstReferent = null;
        foreach ($composter->getUserComposters() as $userC) {
            if ($userC->getComposterContactReceiver()) {
                $user = $userC->getUser();

                $recipients[] = [
                    'email' => $user->getEmail(),
                    'name' => $user->getUsername()
                ];

                $firstReferent = $user;
                $notify_mc = false;
            }
        }

        // On ajoute le maitre composteur à la liste des destinataires
        if ($notify_mc && isset($mc)) {
            $recipients[] = [
                'email' => $mc->getEmail(),
                'name' => $mc->getUsername()
            ];
        }

        $messages = [];

        // Envoie du message à tous les destinataires
        $messages[] = [
            'replyTo'       => ['Email' => $composterContact->getEmail()],
            'to'            => $recipients,
            'subject'       => "[Pom-e] Demande de contact pour le composteur $name",
            'templateId'    => (int) getenv('MJ_CONTACT_FORM_TEMPLATE_ID'),
            'params'     => [
                'email'     => $composterContact->getEmail(),
                'message'   => $composterContact->getMessage()
            ]
        ];

        // Envoie d'une confirmation de message à l'expéditeur
        $confirmation = [
            'to'            => [['email' => $composterContact->getEmail()]],
            'subject'       => '[Pom-e] Demande de contact bien envoyé',
            'templateId'    => (int) getenv('MJ_CONTACT_FORM_USER_CONFIRMED_TEMPLATE_ID'),
        ];

        // On rajoute un référent pour le "ReplyTo"
        if ($firstReferent) {
            $confirmation['replyTo'] = [
                'email' => $firstReferent->getEmail(),
                'name'  => $firstReferent->getUsername()
            ];
        }
        $messages[] = $confirmation;


        $response = $this->email->send($messages);
        $composterContact->setSentByMailjet($response);
    }
}
