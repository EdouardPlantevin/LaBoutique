<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    /**
     * @Route("/commande/create-session/{reference}", name="stripe_create_session")
     */
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
    {
        $product_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order)
        {
            new JsonResponse(['error' => 'order']);
        }

        foreach ($order->getOrdersDetails()->getValues() as $product)
        {
            $product_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => ["https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQUvZ9SncepOS2KQQwjzf17ggovS859dAS3RQ&usqp=CAU"],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }


        $product_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => ["https://www.trouver-un-transporteur.com/file/si734449/transporteur5-fi12104929x290.png"],
                ],
            ],
            'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51HxIhAA1KwotyVEeNqfL8w3jYQFxhDzAjSMW6KNfE8lb91EX1QCRw3Pj7Fg8RNnbSaEDDiIb9YSrQLeDHj2E5P9j00MZjvQxW3');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [$product_for_stripe],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush();

        $reponse = new JsonResponse(['id' => $checkout_session->id]);
        return $reponse;
    }
}
