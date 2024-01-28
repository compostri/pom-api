<?php

namespace App\Controller;

use App\Entity\Composter;
use App\Entity\User;
use App\Entity\UserComposter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailjetWebHookController extends AbstractController
{
    /**
     * @Route("/user_unsub", name="user_unsub")
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if ($data && $data['event'] === 'unsub') {
            $email = $data['email'];
            $mj_list_id = $data['mj_list_id'];

            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $userRepo = $doctrine->getRepository(User::class);
            $composterRepo = $doctrine->getRepository(Composter::class);
            $userComposterRepo = $doctrine->getRepository(UserComposter::class);
            $user = $userRepo->findOneBy(['email' => $email]);
            $composter = $composterRepo->findOneBy(['mailjetListID' => $mj_list_id]);

            if ($user && $composter) {
                $uc = $userComposterRepo->findOneBy(['user'=>$user, 'composter' => $composter]);
                if ($uc instanceof UserComposter) {
                    $uc->setNewsletter(false);
                    $em->persist($uc);
                    $em->flush();
                }
            }
        }
        return $this->json([]);
    }
}
