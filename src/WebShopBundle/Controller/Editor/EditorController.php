<?php

namespace WebShopBundle\Controller\Editor;

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
 * @Security("has_role('ROLE_MOD')")
 */
class EditorController extends Controller
{

    /**
     * @Route("editor/products", name="editor_products")
     */
    public function viewProducts()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();

        return $this->render("editor/products/view.html.twig", ['products' => $products]);
    }

    /**
     * @Route("editor/products/edit/{id}",name="editor_products_edit")
     */
    public function editProduct(Product $product, Request $request)
    {
        $image = $product->getImage();
        $product->setImage(null);
        $form = $this->createForm(ProductType::class, $product);
        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy(['deleted' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \Symfony\Component\HttpFoundation\File\UploadedFile $file
             */
            $file = $product->getImage();
            if ($file != null) {

                $product->setSeller($this->getUser());
                // Generate a unique name for the file before saving it
                $imageName = md5(uniqid()) . '.' . $file->guessExtension();

                // Move the file to the directory where brochures are stored
                $file->move(
                    $this->getParameter('images_directory'),
                    $imageName
                );
            } else $imageName = $image;
            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $product->setImage($imageName);
            // ... persist the $product variable or any other work
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('editor_products');
        }

        return $this->render('editor/products/edit.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories]);
    }

    /**
     * @param Product $product
     * @Route("editor/products/restore/{id}",name="editor_products_restore")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function restoreProduct(Product $product)
    {
        if ($product->getCategory()->isDeleted()) {
            return $this->redirectToRoute('editor_products');
        }
        if($product->getQuantity()<=0){
            $this->addFlash('error','Can not restore product with 0 quantity');
            return $this->redirectToRoute('editor_products');
        }
        $product->setDeleted(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectToRoute('editor_products');
    }

    /**
     * @param Product $product
     * @Route("editor/products/delete/{id}",name="editor_products_delete")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteProduct(Product $product)
    {
        $product->setDeleted(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectToRoute('editor_products');
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("editor/users/all", name="admin_users_all")
     */
    public function allUsers()
    {
        $users= $this->getDoctrine()->getRepository(User::class)->findAll();

       return  $this->render('editor/users/all.html.twig',['users' => $users]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
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

        $form->handleRequest($request);
        dump($user);
        if ($form->isSubmitted()) {
            $roleRequest  = $user->getRoles();
            dump("here");
            $rolesRepository = $this->getDoctrine()->getRepository(Role::class);
            $role = $rolesRepository->findBy(['name' =>$roleRequest]);
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
                'user' => $user
            ));

    }
}
