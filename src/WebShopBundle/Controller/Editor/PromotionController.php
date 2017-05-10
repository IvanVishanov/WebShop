<?php

namespace WebShopBundle\Controller\Editor;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WebShopBundle\Entity\Category;
use WebShopBundle\Entity\Promotion;
use WebShopBundle\Form\PromotionType;

class PromotionController extends Controller
{
    /**
     * @Route("editor/promotions/add{token}",name="editor_promotion_add")
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addPromotion(Request $request,$token)
    {
        if (!$this->isCsrfTokenValid('promotion', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('promotion');
            return $this->redirectToRoute('editor_promotion_view');
        }

        $product = new Promotion();
        $form = $this->createForm(PromotionType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('promotion/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("editor/promotions",name="editor_promotion_view")
     */
    public function viewAllPromotions()
    {
        $promotions = $this->getDoctrine()->getRepository(Promotion::class)->findAll();
        $token = $this->get('security.csrf.token_manager')->refreshToken('promotion');
        return $this->render('promotion/view.html.twig', array(
            'promotions' =>$promotions,
            'token' => $token
        ));
    }
}
