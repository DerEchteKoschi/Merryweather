<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController'
        ]);
    }

    #[Route('/changePassword', name: 'app_profile_change_password')]
    public function changePassword(UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, Request $request): Response
    {
        $old = $request->get('_inputPasswordCurrent', null);
        $new = $request->get('_inputPasswordNew', null);
        $newRepeat = $request->get('_inputPasswordNewRepeat', null);
        if ($old === null || $new === null || $newRepeat === null) {
            $this->addFlash('warning', 'bitte alle Felder ausfüllen');
        } else {
            if ($new !== $newRepeat) {
                $this->addFlash('warning', 'die Wiederholung stimmt nicht überein');
            } else {
                $user = $this->getUser();
                if ($user === null) {
                    $this->addFlash('error', 'Etwas ist schiefgelaufen');

                    return $this->redirectToRoute('app_profile');
                }
                if (!($user instanceof User)) {
                    $user = $userRepository->findBy(['display_name' => $user->getUserIdentifier()]);
                }
                if ($userPasswordHasher->isPasswordValid($user, $old)) {
                    $user->setPassword($userPasswordHasher->hashPassword($user, $new));
                    $userRepository->save($user, true);
                    $this->addFlash('success', 'Das neue Kennwort wurde gespeichert');
                } else {
                    $this->addFlash('warning', 'Das aktuelle Kennwort ist ungültig');
                }
            }
        }

        return $this->redirectToRoute('app_profile');
    }
}
