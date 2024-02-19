<?php

namespace App\Controller;

use App\Entity\ComposterNewsletter;
use App\Service\Mailjet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateComposterNewsletter extends AbstractController
{
    private $maijet;

    public function __construct(Mailjet $mailjet)
    {
        $this->maijet = $mailjet;
    }

    public function __invoke(ComposterNewsletter $data): ComposterNewsletter
    {
        // On récupérer le contactListe ID du composteur
        $composter = $data->getComposter();
        $contactsListID = $composter->getMailjetListID();

        if (!$contactsListID) {
            $composter = $this->maijet->createComposterContactList($composter);
            $contactsListID = $composter->getMailjetListID();

            if ($contactsListID) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($composter);
                $entityManager->flush();
            }
        }

        if ($contactsListID) {
            // On envoie la newsletter avec la bonne liste avec le contenu recut
            $campaignId = $this->maijet->sendCampaign($contactsListID, $data->getSubject(), $data->getMessage(), $composter);

            if ($campaignId) {
                $data->setId($campaignId);
            }
        } else {
            throw new BadRequestHttpException('Le composteur n‘a pas de liste Mailjet de newsletter');
        }

        return $data;
    }
}
