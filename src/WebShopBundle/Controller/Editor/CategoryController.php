<?php

namespace WebShopBundle\Controller\Editor;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use WebShopBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WebShopBundle\Entity\Product;
use WebShopBundle\Form\CategoryType;

class CategoryController extends Controller
{
    /**
     * @Route("/categories/add/{token}",name="category_add")
     * @Security("has_role('ROLE_MOD')")
     */
    public function addCategory(Request $request,$token)
    {
        if (!$this->isCsrfTokenValid('category', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('category');
            return $this->redirectToRoute('category_view');
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash(
                    'error',
                    'Category must be unique'
                );
                return $this->render("editor/category/add.html.twig", ['form' => $form->createView()]);
            }
            $this->addFlash(
                'success',
                'The category was successfully added'
            );
            return $this->redirectToRoute("category_view");
        }

        return $this->render("editor/category/add.html.twig", ['form' => $form->createView()]);
    }

    /**
     * @Route("/categories",name="category_view")
     */
    public function viewCategories()
    {

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $token = $this->get('security.csrf.token_manager')->refreshToken('category');

        return $this->render("editor/category/view.html.twig", ['categories' => $categories,'token'=>$token]);
    }

    /**
     * @param Category $category
     * @Route("/categories/delete/{id}/{token}", name="category_delete")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeCategory(Category $category = null,$token)
    {
        if (!$this->isCsrfTokenValid('category', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('category');
            return $this->redirectToRoute('category_view');
        }
        if($category == null){
            return $this->redirectToRoute('category_view');
        }

        $category->setDeleted(true);

        $em = $this->getDoctrine()->getManager();

        $em->getRepository(Product::class)->deleteProducts($category->getId());

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute("category_view");
    }

    /**
     * @param Category $category
     * @Route("/categories/restore/{id}/{token}",name="category_restore")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function restoreCategory(Category $category = null,$token)
    {
        if (!$this->isCsrfTokenValid('category', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('category');
            return $this->redirectToRoute('category_view');
        }

        if($category == null){
            return $this->redirectToRoute('category_view');
        }

        $category->setDeleted(false);

        $em = $this->getDoctrine()->getManager();

        $em->getRepository(Product::class)->restoreProducts($category->getId());

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute("category_view");
    }
}
