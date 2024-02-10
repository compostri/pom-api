<?php

namespace App\Service;

use App\Entity\Composter;
use App\Entity\Consumer;
use App\Entity\User;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Api\ListsApi;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Configuration;
use Brevo\Client\Model\AddContactToList;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\CreateList;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\GetContacts;
use Brevo\Client\Model\RemoveContactFromList;
use Brevo\Client\Model\SendSmtpEmail;
use Exception;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Component\Security\Core\Security;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class Mailjet
{
    /**
     * @var Client
     */
    private $mj;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var MJML
     */
    private $mjml;

    /**
     * @var MarkdownParserInterface
     */
    private $parser;

    /**
     * @var string
     */
    private $env;

    private $logger;


    /**
     * Mailjet constructor.
     * @param Security $security
     * @param MJML $mjml
     * @param MarkdownParserInterface $parser
     * @param KernelInterface $kernel
     * @param LoggerInterface $logger
     */
    public function __construct(Security $security, MJML $mjml, MarkdownParserInterface $parser, KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->env = $kernel->getEnvironment();
        $this->mj = Configuration::getDefaultConfiguration()->setApiKey('api-key', getenv('BREVO_API_KEY'));

        $this->security = $security;
        $this->mjml = $mjml;
        $this->parser = $parser;
        $this->logger = $logger;
    }


    /**
     * @param array $messages Tableau de tableau [[ 'to' => [], 'Subject' => '', 'TemplateID' => int,  'Variables' => []]]
     * @return bool
     * @throws ApiException
     */
    public function send(array $messages): bool
    {
        $apiInstance = new TransactionalEmailsApi(new Client(), $this->mj);

        $isSend = false;
        foreach ($messages as $message) {
            $m = $message;

            // On a defaut pour le From
            if (! isset($m['from'])) {
                $m['from'] = ['email' => getenv('MAILJET_FROM_EMAIL'), 'name' => getenv('MAILJET_FROM_NAME')];
            }

            $sendSmtpEmail = new SendSmtpEmail($m);
            $response = $apiInstance->sendTransacEmail($sendSmtpEmail);

            $isSend = $isSend || $response->valid();
        }

        return $isSend;
    }

    /**
     * @param string $name
     * @param string $email
     * @return ?int
     */
    public function addContact(string $name, string $email): ?int
    {
        $contactApi = new ContactsApi(new Client(), $this->mj);
        $createContact = new CreateContact([
            'email' => $email,
            'attributes' => ["NOM" => $name],
        ]);


        $mailjetId = null;
        try {
            $result = $contactApi->createContact($createContact);
            $mailjetId = $result->getId();
        } catch (ApiException $e) {
            $responseBody = json_decode($e->getResponseBody());
            if ($responseBody->code === 'duplicate_parameter') {
                $mailjetId = $this->getContact($email);
            }
        }

        return $mailjetId;
    }

    public function getContact(string $email): ?int
    {
        $contactApi = new ContactsApi(new Client(), $this->mj);

        $mailjetId = null;
        try {
            $result = $contactApi->getContactInfo($email);
            $mailjetId = $result->getId();
        } catch (ApiException $e) {
        }

        return $mailjetId;
    }

    /**
     * @param int $contactMailjetId
     * @param array $listsId
     */
    public function addToList(int $contactMailjetId, array $listsId): void
    {
        $apiInstance = new ListsApi(new Client(), $this->mj);
        $contactEmails = new AddContactToList([
            'ids' => [$contactMailjetId]
        ]);

        foreach ($listsId as $listId) {
            try {
                $result = $apiInstance->addContactToList($listId, $contactEmails);
                $this->logger->info($result);
            } catch (Exception $e) {
                $this->logger->error($e);
            }
        }
    }

    /**
     * @param int $contactMailjetId
     * @param array $listsId
     */
    public function removeFromList(int $contactMailjetId, array $listsId): void
    {
        $apiInstance = new ListsApi(new Client(), $this->mj);

        $contactEmails = new RemoveContactFromList([
            'ids' => [$contactMailjetId]
        ]);

        foreach ($listsId as $listId) {
            try {
                $apiInstance->removeContactFromList($listId, $contactEmails);
            } catch (Exception $e) {
            }
        }
    }


    /**
     * @param User $user
     */
    public function addUser(User $user): void
    {
        if (! $user->getMailjetId()) {
            // On ajoute notre contact sur Mailjet
            $mailjetId = $this->addContact($user->getUsername(), $user->getEmail());

            if ($mailjetId) {
                $user->setMailjetId($mailjetId);
            }
        }

        if ($user->getMailjetId()) {

            // On ajoute notre contact aux composteurs
            $compostersMailjetListId = [];
            foreach ($user->getUserComposters() as $uc) {
                $mailjetListId = $uc->getComposter()->getMailjetListID();

                if ($mailjetListId && $uc->getNewsletter()) {
                    $compostersMailjetListId[] = $mailjetListId;
                }
            }
            // On l'ajoute à la newsletter de compostri
            if ($user->getIsSubscribeToCompostriNewsletter()) {
                $compostersMailjetListId[] = (int) getenv('MJ_COMPOSTRI_NEWSLETTER_CONTACT_LIST_ID');
            }

            if (count($compostersMailjetListId) > 0) {
                $this->addToList($user->getMailjetId(), $compostersMailjetListId);
            }
        }
    }


    /**
     * @param int $contactMailjetId
     */
    public function getContactContactsLists(int $contactMailjetId)
    {
        //return $this->mj->get(Resources::$ContactGetcontactslists, ['id' => $contactMailjetId]);
    }


    /**
     * @param string $listId
     * @param string $subject
     * @param string $content
     */
    public function createCampaignDraft(string $listId, string $subject)
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $body = [
                'EditMode'              => 'mjml',
                'IsStarred'             => 'false',
                'IsTextPartIncluded'    => 'true',
                'ReplyEmail'            => $user->getEmail(),
                'Title'                 => $subject,
                'ContactsListID'        => $listId,
                'Locale'                => 'fr_FR',
                'Sender'                => 'Compostri',
                'SenderEmail'           => getenv('MAILJET_FROM_EMAIL'),
                'SenderName'            => getenv('MAILJET_FROM_NAME'),
                'Subject'               => $subject
            ];
            //return $this->mj->post(Resources::$Campaigndraft, ['body' => $body]);
        }
    }


    /**
     * @param int $campaignId
     * @param string $content
     * @param Composter $composter
     */
    public function addCampaignDraftContent(int $campaignId, string $content, Composter $composter)
    {
        $html = $this->mjml->getHtml(
            str_replace(
                ['{{message}}', '{{composterURL}}','{{composterName}}'],
                [$this->parser->transformMarkdown($content), getenv('FRONT_DOMAIN').'/composteur/' . $composter->getSlug(), $composter->getName()],
                file_get_contents(__DIR__ . '/../../templates/mjml/composteur-newsletter.mjml')
            )
        );
        $body = [
            'Html-part'     => $html,
            'Text-part'     => $content
        ];

        //return $this->mj->post(Resources::$CampaigndraftDetailcontent, ['id' => $campaignId, 'body' => $body]);
    }

    /**
     * @param string $listId
     * @param string $subject
     * @param string $content
     * @param Composter $composter
     * @return string|null id of campaign or null on error
     */
    public function sendCampaign(string $listId, string $subject, string $content, Composter $composter): ?string
    {
        // Créer un brouillont : POST 	/campaigndraft
        $response = $this->createCampaignDraft($listId, $subject);

        if ($response->success()) {
            $draftData = $response->getData();
            $campaignDraftId = $draftData[0]['ID'];

            // Ajouter du contenu : POST /campaigndraft/{draft_ID}/detailcontent
            $response = $this->addCampaignDraftContent($campaignDraftId, $content, $composter);

            if ($response->success() && $this->env === 'prod') {
                // Et enfin l'envoyer : POST /campaigndraft/{draft_ID}/send
                //$response = $this->mj->post(Resources::$CampaigndraftSend, ['id' => $campaignDraftId]);
            }
        }


        return $campaignDraftId;
    }


    /**
     * @param Composter $composter
     * @return Composter
     */
    public function createComposterContactList(Composter $composter): Composter
    {
        $contactListId = $composter->getMailjetListID();

        if (! $contactListId) {
            $slug = $composter->getName();

            $apiInstance = new ListsApi(new Client(), $this->mj);

            $createList = new CreateList([
                'name' => $slug,
                'folderId' => (int) getenv('BREVO_COMPOSTEURS_FOLDER')
            ]);


            $contactListId = null;
            try {
                $result = $apiInstance->createList($createList);

                $contactListId = $result->getId();
            } catch (ApiException $e) {
            }

            if ($contactListId) {
                $composter->setMailjetListID($contactListId);
            }
        }

        return $composter;
    }

    /**
     */
    public function getMj()
    {
        return $this->mj;
    }
}
