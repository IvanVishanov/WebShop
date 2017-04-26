<?php

namespace WebShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use WebShopBundle\Entity\Product;
use WebShopBundle\Entity\User;

class CartController extends Controller
{
    /**
     * @Route("/product/addcart/{id}", name="cart_add")
     */
    public function AddToCart(Product $product)
    {
        $product->setQuantity(1);
        /**
         * @var User
         */
        $user = $this->getUser();
        $user->addCart($product);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart", name="cart")
     */
    public function showCart()
    {
        $products = $this->getUser()->getCart();
        return $this->render('cart/view.html.twig', ['products' => $products]);
    }

    /**
     * @Route("/product/removecart/{id}", name="cart_remove")
     */
    public function removeFromCart(Product $product)
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        $cart = $user->getCart();
        $cart = array_values(array_filter($cart, function ($cartProduct) use ($product) {
            return $cartProduct->getId() !== $product->getId();
        }));
        $user->setCart($cart);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('cart');
    }
}
