<?php

namespace WebShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WebShopBundle\Entity\Category;
use WebShopBundle\Entity\Product;
use WebShopBundle\Form\ProductType;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @Route("/products/upload", name="products_upload")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction(Request $request)
    {
        $product = new Product();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var \Symfony\Component\HttpFoundation\File\UploadedFile $file
             */
            $file = $product->getImage();
            $product->setSeller($this->getUser());
            // Generate a unique name for the file before saving it
            $imageName = md5(uniqid()) . '.' . $file->guessExtension();

            // Move the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('images_directory'),
                $imageName
            );

            // Update the 'brochure' property to store the PDF file name
            // instead of its contents
            $product->setImage($imageName);
            // ... persist the $product variable or any other work
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('product/upload.html.twig', array(
            'form' => $form->createView(),
            'categories' => $categories
        ));
    }

    /**
     * @Route("/products/search", name="products_search")
     */
    public function searchProducts(Request $request)
    {
        $categoryId = $request->get('category');
        $search = $request->get('search');
        $products = $this->getDoctrine()->getRepository(Product::class)->findBy(['category' => $categoryId]);
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $productsToShow = [];
        foreach ($products as $product)
        {
            if (strpos(strtolower($product->getName()), $search) !== false) {
                $productsToShow[] = $product;
            }
        }
        return $this->render('product/viewAll.html.twig',
            [
                'products' => $productsToShow,
                'categories' => $categories,
            ]
        );
    }

    /**
     * @Route("/products/all", name="products_all")
     * @Method("GET")
     */
    public function viewAllProductsAction()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $token = $this->get('security.csrf.token_manager')->getToken('asd');
        return $this->render('product/viewAll.html.twig',
            [
                'products' => $products,
                'categories' => $categories,
                'token'=>$token
            ]
        );
    }
}
