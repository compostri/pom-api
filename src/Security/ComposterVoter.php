<?php

// src/Security/PostVoter.php

namespace App\Security;

use App\Entity\Composter;
use App\Entity\Permanence;
use App\Entity\User;
use App\Repository\ComposterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ComposterVoter extends Voter
{
    // these strings are just invented: you can use anything
    public const OPENER = 'Opener';
    public const REFERENT = 'Referent';
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ComposterRepository
     */
    private $composterRepository;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->composterRepository = $entityManager->getRepository(Composter::class);
    }

    protected function supports($attribute, $subject): bool
    {
        $supports = in_array($attribute, [self::OPENER, self::REFERENT], true) &&
            ($subject instanceof Composter || $subject instanceof Permanence);

        if (!$supports) {
            // Si $subject est null on est peut être sur une création
            $currentRequest = $this->requestStack->getCurrentRequest();
            $supports = null === $subject && $currentRequest instanceof Request && 'POST' === $currentRequest->getMethod();
        }

        return $supports;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $grant = false;

        $roles = $token->getRoleNames();
        $user = $token->getUser();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return true;
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        // $subject is a Composter or Permanence or null if POST method
        $composter = null;
        if ($subject instanceof Composter) {
            $composter = $subject;
        } elseif ($subject instanceof Permanence) {
            $composter = $subject->getComposter();
        } else {
            // On est sur la création d'une entité
            if ($currentRequest && '/permanences' === $currentRequest->getPathInfo()) {
                // On tente de créer une permanence
                $request_body = json_decode($currentRequest->getContent(), false);
                $composter_string = $request_body->composter;
                $composter_url_array = explode('/', $composter_string);
                $composter_slug = end($composter_url_array);
                $composter = $this->composterRepository->findOneBy(['slug' => $composter_slug]);
            }
        }

        if (!$composter instanceof Composter) {
            return false;
        }

        // Les référents on les même droit que les ouvreurs
        $attribute_array = $attribute === $this::OPENER ? [$this::OPENER, $this::REFERENT] : $attribute;

        // On vérifie que le user est bien rattaché au composteur
        $current_role = null;
        foreach ($user->getUserComposters() as $userComposter) {
            if (in_array($userComposter->getCapability(), $attribute_array, true) &&
            $userComposter->getComposter()->getId() === $composter->getId()) {
                $current_role = $userComposter->getCapability();
                $grant = true;
            }
        }

        // Dans le cas d'un ouvreur on vérifie qu'il modifie ou supprime uniquement une permance ou il est inscrit
        if ($grant && $subject instanceof Permanence && $current_role === $this::OPENER) {
            $grant = false;
            if ($currentRequest && 'PUT' === $currentRequest->getMethod()) {
                // On récupére les ids des ouvreurs de la permanence
                $openers = $subject->getOpeners();
                $openers_ids = array_map(static function (User $opener) {
                    return $opener->getId();
                }, $openers->toArray());

                // On récupérer les ids des ouvreurs de la requette
                $request_body = json_decode($currentRequest->getContent(), false);

                // Si il modifie la liste des ouveurs
                if (property_exists($request_body, 'openers')) {
                    $request_openers = $request_body->openers;
                    $openers_request_ids = array_map(
                        static function (string $opener) {
                            $opener_a = explode('/', $opener);

                            return (int) end($opener_a);
                        },
                        $request_openers
                    );

                    // On vérifie qu'il n'y a que le user comme différence
                    $diff = null;
                    if (count($openers_ids) > count($openers_request_ids)) {
                        $diff = array_diff($openers_ids, $openers_request_ids);
                    } else {
                        $diff = array_diff($openers_request_ids, $openers_ids);
                    }

                    $grant = 0 === count($diff) || (1 === count($diff) && array_shift($diff) === $user->getID());
                } else {
                    // TODO peut être vérifier ici qu'il ne modifie que les données des stats
                    $grant = true;
                }
            }
        }

        return $grant;
    }
}
