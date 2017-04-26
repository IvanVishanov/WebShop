<?php

namespace WebShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WebShopBundle\Entity\Role;
use WebShopBundle\Entity\User;
use WebShopBundle\Form\UserType;

class UserController extends Controller
{
    /**
     * @Route("/register",name="register")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $roleRepository = $this->getDoctrine()->getRepository(Role::class);
            $userRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);
            $user->addRole($userRole);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("security_login");
        }
        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/profile",name="user_profile")
     */
    public function profileAction()
    {
        /**
         * @var User
         */
        $user = $this->getUser();
        dump($user);
       $product =  $user->getProducts();
       dump($product);
       return $this->render('user/profile.html.twig',['products'=>$product]);
    }
}
