<?php


namespace App\Service;


use App\Entity\Composter;
use App\Entity\Consumer;
use App\Entity\User;
use Mailjet\Client;
use Mailjet\Resources;
use Mailjet\Response;
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
     * Mailjet constructor.
     * @param Security $security
     * @param MJML $mjml
     * @param MarkdownParserInterface $parser
     */
    public function __construct( Security $security, MJML $mjml, MarkdownParserInterface $parser )
    {
        $this->mj = new Client(
            getenv('MJ_APIKEY_PUBLIC'),
            getenv('MJ_APIKEY_PRIVATE'),
            true,
            ['version' => 'v3']
        );

        $this->security = $security;
        $this->mjml = $mjml;
        $this->parser = $parser;
    }


    /**
     * @param array $messages   Tableau de tableau [[ 'To' => [], 'Subject' => '', 'TemplateID' => int,  'Variables' => []]]
     * @return Response
     */
    public function send( array $messages ): Response
    {

        $body = [ 'Messages' => []];

        foreach ( $messages as $message ){

            $m = $message;

            // On a defaut pour le From
            if( ! isset(  $m['From'] ) ){
                $m['From'] = ['Email' => getenv('MAILJET_FROM_EMAIL'), 'Name' => getenv('MAILJET_FROM_NAME')];
            }

            $m['TemplateLanguage'] = true;

            $body['Messages'][] = $m;


        }

        return $this->mj->post(Resources::$Email, ['body' => $body], ['version' => 'v3.1']);
    }

    /**
     * @param string $name
     * @param string $email
     * @return Response
     */
    public function addContact( string $name, string $email ) : Response
    {


        $body = [
            'IsExcludedFromCampaigns'   => 'false',
            'Name'                      => $name,
            'Email'                     => $email
        ];

        return $this->mj->post(Resources::$Contact, ['body' => $body], ['version' => 'v3']);
    }


    /**
     * @param int $contactMailjetId
     * @param array $listsId
     * @return Response
     */
    public function addToList( int $contactMailjetId, array $listsId ) : Response
    {
        $contactList = [];
        foreach ( $listsId as $lId ){
            $contactList[] = [
                    'Action' => 'addnoforce',
                    'ListID' => $lId
                ];
        }
        $body = [
            'ContactsLists' => $contactList
        ];

        return $this->mj->post(Resources::$ContactManagecontactslists, ['id' => $contactMailjetId, 'body' => $body]);
    }


    /**
     * @param Consumer $consumer
     * @return Response|null
     */
    public function addConsumer( Consumer $consumer ) : ?Response
    {

        $response = null;

        if( ! $consumer->getMailjetId() ){
            // On ajoute notre contact sur Mailjet
            $response = $this->addContact( $consumer->getUsername(), $consumer->getEmail() );

            if( $response->success() ){

                $contactData = $response->getData();
                $consumer->setMailjetId( $contactData[0]['ID'] );
            }
        }


        if( $consumer->getMailjetId() ){

            // On ajoute notre contact aux composteurs
            $compostersMailjetListId = [];
            foreach ( $consumer->getComposters() as $composter ){
                $mailjetListId = $composter->getMailjetListID();

                if( $mailjetListId ){
                    $compostersMailjetListId[] = $mailjetListId;
                }
            }
            // On l'ajoute à la newsletter de compostri
            if( $consumer->getSubscribeToCompostriNewsletter() ){
                $compostersMailjetListId[] = getenv('MJ_COMPOSTRI_NEWSLETTER_CONTACT_LIST_ID');
            }

            if( count( $compostersMailjetListId ) > 0 ){
                $response = $this->addToList( $consumer->getMailjetId(), $compostersMailjetListId );
            }


        }

        return $response;
    }


    /**
     * @param int $contactMailjetId
     * @return Response
     */
    public function getContactContactsLists( int $contactMailjetId ) : Response
    {
        return $this->mj->get(Resources::$ContactGetcontactslists, ['id' => $contactMailjetId]);
    }


    /**
     * @param string $listId
     * @param string $subject
     * @param string $content
     * @return Response
     */
    public function createCampaignDraft( string $listId, string $subject ) : Response
    {

        $user = $this->security->getUser();

        if( $user instanceof User ){

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
            return $this->mj->post(Resources::$Campaigndraft, ['body' => $body]);
        }

    }


    /**
     * @param int $campaignId
     * @param string $content
     * @return Response
     */
    public function addCampaignDraftContent( int $campaignId, string $content ) : Response
    {

        $html = $this->mjml->getHtml( str_replace(
            '{{message}}',
            $this->parser->transformMarkdown( $content ),
            file_get_contents(__DIR__ . '/../../templates/mjml/composteur-newsletter.mjml') )
        );
        $body = [
            'Html-part'     => $html,
            'Text-part'     => $content
        ];

        return $this->mj->post(Resources::$CampaigndraftDetailcontent, ['id' => $campaignId, 'body' => $body]);
    }

    /**
     * @param string $listId
     * @param string $subject
     * @param string $content
     * @return string|null id of campaign or null on error
     */
    public function sendCampaign( string $listId, string $subject, string $content ) : ?string
    {
        // Créer un brouillont : POST 	/campaigndraft
        $response = $this->createCampaignDraft( $listId, $subject );

        if( $response->success() ){
            $draftData = $response->getData();
            $campaignDraftId = $draftData[0]['ID'];

            // Ajouter du contenu : POST /campaigndraft/{draft_ID}/detailcontent
            $response = $this->addCampaignDraftContent( $campaignDraftId, $content );

            if( $response->success() ){
                // Et enfin l'envoyer : POST /campaigndraft/{draft_ID}/send
                $response = $this->mj->post(Resources::$CampaigndraftSend, ['id' => $campaignDraftId]);
            }

        }


        return $campaignDraftId;
    }


    /**
     * @param Composter $composter
     * @return Composter
     */
    public function createComposterContactList( Composter $composter ) : Composter
    {

        $contactListId = $composter->getMailjetListID();

        if( ! $contactListId ) {

            $slug = $composter->getName();

            $body = [
                'Name' => $slug
            ];
            $response = $this->mj->post(Resources::$Contactslist, ['body' => $body]);

            if( $response->getStatus() === 400 ){
                // La liste existe déja
                $response = $this->mj->get(Resources::$Contactslist, [ 'filters' => ['Name' => $slug]]);
            }

            if( in_array($response->getStatus(), [200, 201], true) ){
                $responseData = $response->getData();
                $contactListId = $responseData[0]['ID'];

                $composter->setMailjetListID( $contactListId );
            }
        }

        return $composter;
    }

    /**
     * @return Client
     */
    public function getMj(): Client
    {
        return $this->mj;
    }

}