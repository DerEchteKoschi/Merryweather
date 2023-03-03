<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\PasswordValidationContainer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/changePassword', name: 'app_profile_change_password')]
    public function changePassword(ValidatorInterface $validator, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, Request $request): Response
    {
        $user = $this->getUser();
        if (!($user instanceof User) && $user !== null) {
            $user = $userRepository->findOneBy(['phone' => $user->getUserIdentifier()]);
        }
        if ($user === null) {
            $this->addFlash('error', 'Etwas ist schiefgelaufen');

            return $this->redirectToRoute('app_profile');
        }

        $current = $request->get('_inputPasswordCurrent', null);
        $new = $request->get('_inputPasswordNew', null);
        $newRepeat = $request->get('_inputPasswordNewRepeat', null);

        $passwordValidationContainer = new PasswordValidationContainer($current, $new, $newRepeat);

        $violations = $validator->validate($passwordValidationContainer);
        $violationTypes = ['danger' => 0, 'warning' => 0];
        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                $severity = 'danger';
                if (($violation instanceof ConstraintViolation) && isset($violation->getConstraint()?->payload['severity'])) {
                    $severity = $violation->getConstraint()->payload['severity'];
                }
                $violationTypes[$severity]++;
                $this->addFlash($severity, $violation->getMessage());
            }
            if ($violationTypes['danger'] > 0) {
                return $this->redirectToRoute('app_profile');
            }
        }

        if ($userPasswordHasher->isPasswordValid($user, $current)) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $new));
            $userRepository->save($user, true);
            $this->addFlash('success', 'Das neue Kennwort wurde gespeichert');
        } else {
            $this->addFlash('danger', 'Das aktuelle Kennwort ist ungültig');
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig');
    }
}
