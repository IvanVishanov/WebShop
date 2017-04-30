<?php

namespace WebShopBundle\Controller\Editor;

use WebShopBundle\Entity\BoughtProducts;
use WebShopBundle\Form\UserEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WebShopBundle\Entity\Category;
use WebShopBundle\Entity\Product;
use WebShopBundle\Entity\Role;
use WebShopBundle\Entity\User;
use WebShopBundle\Form\CategoryType;
use WebShopBundle\Form\ProductType;

/**
 * Class EditorController
 * @package WebShopBundle\Controller
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends Controller
{

    /**
     * @Route("editor/users/all", name="admin_users_all")
     */
    public function allUsers()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('editor/users/all.html.twig', ['users' => $users]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route ("/admin/editProfile/{id}" , name="admin_editProfile")
     */
    public function adminEditProfileAction($id, Request $request)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $form = $this->createForm(UserEditType::class, $user);
        $currentPassword = $user->getPassword();
        $boughProducts = $user->getBoughtProduct();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $roleRequest = $user->getRoles();
            dump("here");
            $rolesRepository = $this->getDoctrine()->getRepository(Role::class);
            $role = $rolesRepository->findBy(['name' => $roleRequest]);
            $user->setRoles($role);
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

            return $this->redirectToRoute("admin_users_all");
        }
        return $this->render(
            'editor/users/editProfile.html.twig',
            array('form' => $form->createView(),
                'user' => $user,
                'boughtProducts' => $boughProducts
            ));

    }

    /**
     * @Route ("/admin/deleteBoughtProduct/{userId}/{boughtProductId}" , name="admin_delete_boughtProduct")
     * @param $userId
     * @param $boughtProductId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteBoughtProduct($userId,$boughtProductId)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=>$userId]);
        $boughtProduct = $this->getDoctrine()->getRepository(BoughtProducts::class)->findOneBy(['id'=>$boughtProductId]);
        if($user == null || $boughtProduct){
            return $this->redirectToRoute('admin_users_all');
        }

        $user->removeBoughtProduct($boughtProduct);

        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return $this->redirectToRoute('admin_users_all');
    }
}
