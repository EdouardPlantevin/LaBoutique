<?php

namespace App\Controller;

use App\Classes\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if (!$search_email)
            {
                $password = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($password);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à present vous connecter à votre compte";

                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstName()."<br />Je suis Edouard Plantevin, élève chez Openclassrooms dans le parcours Développeur PHP/Symfony<br>Le site 'La Boutique' que vous avez vu ou que vous aller parcourir est un site portefolio vous montrant mes compétences et mes capacités<br>Je suis actuellement à la recherche d'une entreprise qui souhaiterai travailler avec moi dans le cadre d'une alternance<br>Je suis disponible à l'adresse : plantevin.contact@gmail.com ou bien par téléphone au 07 71 15 56 56<br>Merci<br>Edouard Plantevin";
                $mail->send($user->getEmail(), $user->getFirstName(), 'Bienvenue sur la boutique by Edouard Plantevin', $content);
            }
            else
            {
                $notification = "L'email que vous avez renseigné existe déjà";
            }

        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
        ]);
    }
}
