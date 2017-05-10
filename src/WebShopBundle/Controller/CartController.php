<?php

namespace WebShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WebShopBundle\Entity\Product;
use WebShopBundle\Entity\Quantity;
use WebShopBundle\Entity\User;

/**
 * Class CartController
 * @package WebShopBundle\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class CartController extends Controller
{
    /**
     * @Route("/cart/addproduct/{id}", name="cart_add")
     */
    public function AddToCart(Product $product = null)
    {
        if ($product === null ||
            $product->getQuantity() <= 0 ||
            $product->isDeleted() == true ||
            $product->getSeller() == $this->getUser()
        ) {
            return $this->redirectToRoute("homepage");
        }
        $cart = $this->getUser()->getCart();
        $quantities = $cart->getQuantities();

        if ($quantity = array_filter($quantities->toArray(), function ($quant) use ($product) {
            return $quant->getProduct() == $product;
        })
        ) {
            $quantity = array_values($quantity);
            $quantity[0]->setQuantity($quantity[0]->getQuantity() + 1);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('cart');
        }

        $quantity = new Quantity();
        $quantity->setQuantity(1);

        $cart->addQuantity($quantity);
        $product->addQuantity($quantity);

        $em = $this->getDoctrine()->getManager();
        $em->persist($quantity);
        $em->flush();

        return $this->redirectToRoute('cart');

    }

    /**
     * @Route("/cart", name="cart")
     */
    public function showCart()
    {

        $cart = $this->getUser()->getCart();

        $quantities = $cart->getQuantities();
        foreach ($quantities as $quantity) {
            if ($quantity->getProduct()->isDeleted()) {
                $cart->removeQuantity($quantity);
            } else if ($quantity->getProduct()->getQuantity() <= 0) {
                $cart->removeQuantity($quantity);
            } else if ($quantity->getQuantity() > $quantity->getProduct()->getQuantity()) {
                $quantity->setQuantity($quantity->getProduct()->getQuantity());
            }
        }

        $token = $this->get('security.csrf.token_manager')->refreshToken('buy');

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->render('cart/view.html.twig', [
            'cart' => $cart,
            'quantities' => $quantities->toArray(),
            'token' => $token
        ]);
    }

    /**
     * @Route("/cart/clear", name="cart_clear")
     */
    public function clearCart()
    {
        $cart = $this->getUser()->getCart();
        $quantities = $cart->getQuantities();

        foreach ($quantities as $quantity) {
            $cart->removeQuantity($quantity);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($cart);
        $em->flush();

        return $this->redirectToRoute("cart");
    }

    /**
     * @Route("/cart/remove/product/{id}", name="cart_remove")
     */
    public function removeFromCart(Quantity $quantity = null)
    {
        if ($quantity === null) {
            return $this->redirectToRoute("homepage");
        }
        $cart = $this->getUser()->getCart();

        $cart->removeQuantity($quantity);

        $em = $this->getDoctrine()->getManager();
        $em->persist($cart);
        $em->flush();

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/reload", name="cart_reload")
     */
    public function cartReload(Request $request)
    {
        $cart = $this->getUser()->getCart();
        $quantities = $cart->getQuantities();

        foreach ($quantities as $quantity) {
            $productQuantity = $quantity->getProduct()->getQuantity();
            $reqQuantity = $request->get($quantity->getId());

            if ($productQuantity < $reqQuantity) {
                $this->addFlash(
                    'error',
                    'You want more quantity than available'
                );
                break;
            }
            if ($reqQuantity <= 0) {
                $this->addFlash(
                    'error',
                    'Quantity can not be less or equal to 0'
                );
                break;
            }
            $quantity->setQuantity($request->get($quantity->getId()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($quantity);
            $em->flush();
        }

        return $this->redirectToRoute("cart");
    }

    /**
     * @Route("/cart/order{token}", name="cart_order")
     */
    public function approveOrder($token)
    {
        if (!$this->isCsrfTokenValid('buy', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('buy');
            return $this->redirectToRoute('homepage');
        }

        $cart = $this->getUser()->getCart();

        if (!$cart->getQuantities()->toArray()) {
            return $this->redirectToRoute("cart");
        }

        $error = false;
        $quantities = $cart->getQuantities();

        $token = $this->get('security.csrf.token_manager')->refreshToken('buy');

        if ($cart->getTotal() > $this->getUser()->getCash()) {
            $this->addFlash(
                'error',
                'You do not have enough money'
            );
            $error = true;
        }
        return $this->render('cart/order.html.twig', ['quantities' => $quantities, 'error' => $error, 'token' => $token]);
    }
}
