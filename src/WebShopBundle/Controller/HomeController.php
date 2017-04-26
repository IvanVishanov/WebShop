<?php

namespace WebShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WebShopBundle\Entity\Product;


class HomeController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        return $this->render('default/index.html.twig',['products' => $products]);
    }
}
