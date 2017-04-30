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
     * @Route("/categories/add",name="category_add")
     * @Security("has_role('ROLE_MOD')")
     */
    public function addCategory(Request $request)
    {
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
        return $this->render("editor/category/view.html.twig", ['categories' => $categories]);
    }

    /**
     * @param Category $category
     * @Route("/categories/delete{id}",name="category_delete")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeCategory(Category $category)
    {
        $category->setDeleted(true);

        $em = $this->getDoctrine()->getManager();

        $em->getRepository(Product::class)->deleteProducts($category->getId());

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute("category_view");
    }

    /**
     * @param Category $category
     * @Route("/categories/restore{id}",name="category_restore")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function restoreCategory(Category $category)
    {
        $category->setDeleted(false);

        $em = $this->getDoctrine()->getManager();

        $em->getRepository(Product::class)->restoreProducts($category->getId());

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute("category_view");
    }
}
