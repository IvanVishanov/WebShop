<?php

namespace WebShopBundle\Controller\Editor;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WebShopBundle\Entity\Category;
use WebShopBundle\Entity\Product;
use WebShopBundle\Form\ProductType;

/**
 * Class ProductController
 * @package WebShopBundle\Controller\Editor\ProductController
 * @Security("has_role('ROLE_MOD')")
 */
class ProductController extends Controller
{
    /**
     * @Route("editor/products", name="editor_products")
     */
    public function viewProducts()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();

        $token = $this->get('security.csrf.token_manager')->refreshToken('product');

        return $this->render("editor/products/view.html.twig", ['products' => $products,'token' =>$token]);
    }

    /**
     * @Route("editor/products/edit/{id}/{token}",name="editor_products_edit")
     */
    public function editProduct(Product $product = null, Request $request,$token)
    {
        if (!$this->isCsrfTokenValid('product', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('product');
            return $this->redirectToRoute('editor_products');
        }
        if($product == null){
            return $this->redirectToRoute('editor_products');
        }
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

                // Generate a unique name for the file before saving it
                $imageName = md5(uniqid()) . '.' . $file->guessExtension();

                // Move the file to the directory where brochures are stored
                $file->move(
                    $this->getParameter('images_directory'),
                    $imageName
                );
            } else $imageName = $image;

            $product->setImage($imageName);

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
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
     * @Route("editor/products/restore/{id}/{token}",name="editor_products_restore")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function restoreProduct(Product $product,$token)
    {
        if (!$this->isCsrfTokenValid('product', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('product');
            return $this->redirectToRoute('editor_products');
        }

        if ($product->getCategory()->isDeleted()) {
            return $this->redirectToRoute('editor_products');
        }
        if ($product->getQuantity() <= 0) {
            $this->addFlash('error', 'Can not restore product with 0 quantity');
            return $this->redirectToRoute('editor_products');
        }
        $product->setDeleted(false);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectToRoute('editor_products');
    }

    /**
     * @param Product $product
     * @Route("editor/products/delete/{id}/{token}",name="editor_products_delete")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteProduct(Product $product,$token)
    {
        if (!$this->isCsrfTokenValid('product', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('product');
            return $this->redirectToRoute('editor_products');
        }

        $product->setDeleted(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->redirectToRoute('editor_products');
    }
}
