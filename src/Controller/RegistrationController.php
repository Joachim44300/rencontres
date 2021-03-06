<?php

namespace App\Controller;

use App\Entity\Profil;
use App\Entity\User;
use App\Form\DetailFormType;
use App\Form\ProfilFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\AppAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="registration_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, AppAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setDateCreated(new \DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register/profil/", name="registration_registerProfil")
     */
    public function registerProfil( Request $request, EntityManagerInterface $entityManager, UserInterface $user):Response
    {
        // Cr??e une instance de l'entit?? que le form sert ?? cr??er
        $profil = new Profil();
        // Cr??e une instance de la classe de formulaire que l'on assicie ?? notre formulaire
        $profilForm = $this->createForm(ProfilFormType::class, $profil);
        // On prend les donn??es du formulaire soumis, et les injecte dans mon $profil
        $profilForm->handleRequest($request);

        // Si le formulaire est soumis
        if ($profilForm->isSubmitted() && $profilForm->isValid()) {

            // Hydrate les propri??t??s qui sont encore null
             $profil->setUser($user);

            // Sauvegarde en Bdd
            $entityManager->persist($profil);
            $entityManager->flush();

            // On ajoute un message flash
            $this->addFlash("success", "Le message a ??t?? enregistr??");
        }

        return $this->render('registration/profil.html.twig', [
            "profilForm" => $profilForm->createView(),
        ]);
    }

    /**
     * @Route("/register/profil/{id}", name="registration_profil_detail")
     */
    public function ProfilDetail($id, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager) : Response
    {
        $user = $userRepository->find($id);

        // Cr??e une instance de l'entit?? que le form sert ?? cr??er
        $userInfo = new User();
        $profilInfo = new Profil();

        // on injecte des donn??es
        $currentUsername = $this->getUser()->getUsername();
        $currentEmail = $this->getUser()->getEmail();
        $currentDate = $this->getUser()->getProfil()->getDateOfBirth();
        $currentCity = $this->getUser()->getProfil()->getCity();
        $userInfo->setUsername($currentUsername);
        $userInfo->setEmail($currentEmail);
        $profilInfo->setDateOfBirth($currentDate);
        $profilInfo->setCity($currentCity);


        // Cr??e une instance de la classe de formulaire que l'on assicie ?? notre formulaire
        $detailForm = $this->createForm(DetailFormType::class, ['user' => $userInfo, 'profil' => $profilInfo]);

        // On prend les donn??es du formulaire soumis, et les injecte dans mon $profil
        $detailForm->handleRequest($request);

        return $this->render('registration/detail.html.twig', [
                'user' => $user,
                'detailForm' => $detailForm->createView(),
        ]);
    }
}
