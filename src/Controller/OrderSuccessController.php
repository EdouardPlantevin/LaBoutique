<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classes\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_success")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser())
        {
            return $this->redirectToRoute('home');
        }

        if ($order->getState() == 0)
        {
            $cart->remove();
            $order->setState(1);
            $this->entityManager->flush();

            $mail = new Mail();
            $content = "ReBonjour ".$order->getUser()->getFirstName()."<br />Je croisent les doigts pour que vous ayez réellement mit vos codes de carte bleu :)";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstName(), 'Votre commande La Boutique est bien validée', $content);
        }


        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
