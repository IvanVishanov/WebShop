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
        $products = $user->getProducts();
        $boughtProducts = $user->getBoughtProduct();

        $token = $this->get('security.csrf.token_manager')->refreshToken('user');

        foreach ($boughtProducts as $boughtProduct) {
            if ($boughtProduct->getQuantity() <= 0) {
                $this->getUser()->removeBoughtProduct($boughtProduct);
            }
        }
        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'products' => $products,
            'boughProducts' => $boughtProducts,
            'token' => $token
        ]);
    }

    /**
     * @Route("/profile/edit/",name="user_editprofile")
     */
    public function profileEditAction(Request $request )
    {

        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $currentPassword = $user->getPassword();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (empty($user->getPassword())) {
                $user->setPassword($currentPassword);
            } else {
                $newPassword = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPassword());
                $user->setPassword($newPassword);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("user_profile");
        }
        return $this->render(
            'user/editProfile.html.twig',
            array('form' => $form->createView(),
                'user' => $user
            ));
    }
}
