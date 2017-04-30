<?php

namespace WebShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use WebShopBundle\Entity\BoughtProducts;
use WebShopBundle\Entity\Category;
use WebShopBundle\Entity\Product;
use WebShopBundle\Entity\User;
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
        $form = $this->createForm(ProductType::class, $product);
        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy(['deleted' => false]);
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
        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy(['deleted' => false]);
        $productsToShow = [];

        foreach ($products as $product) {
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
     */
    public function viewAllProductsAction()
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findProductsWithQuantity();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy(['deleted' => false]);
        $token = $this->get('security.csrf.token_manager')->getToken('asd');
        return $this->render('product/viewAll.html.twig',
            [
                'products' => $products,
                'categories' => $categories,
                'token' => $token
            ]
        );
    }

    /**
     * @Route("/products/buy{token}", name="products_buy")
     */
    public function buyProduct($token)
    {
        if (!$this->isCsrfTokenValid('buy', $token)) {
            $this->get('security.csrf.token_manager')->refreshToken('buy');
            return $this->redirectToRoute('homepage');
        }

        $user = $this->getUser();
        $cart = $user->getCart();
        $user->setCash($user->getCash() - $cart->getTotal());
        $quantities = $cart->getQuantities();
        $boughtProducts = $user->getBoughtProduct();

        foreach ($quantities as $quantity) {

            if ($boughtProduct = array_filter($boughtProducts->toArray(), function ($boughtProduct) use ($quantity) {
                return $quantity->getProduct() == $boughtProduct->getProduct();
            })
            ) {

                $boughtProduct = array_values($boughtProduct);

                $boughtProduct[0]->setQuantity($boughtProduct[0]->getQuantity() + $quantity->getQuantity());


            } else {
                $boughtProduct = new BoughtProducts();

                $user->addBoughtProduct($boughtProduct);
                $quantity->getProduct()->addBoughtProduct($boughtProduct);
                $boughtProduct->setQuantity($quantity->getQuantity());
            }

            $seller = $quantity->getProduct()->getSeller();
            $seller->setCash($seller->getCash() + $quantity->getTotal());

            $quantity->getProduct()->setQuantity($quantity->getProduct()->getQuantity() - $quantity->getQuantity());

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }
        return $this->redirectToRoute('cart_clear');
    }

    /**
     * @Route("/products/sell/{id}", name="products_sell")
     * @param BoughtProducts $boughtProduct
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function sellProduct(BoughtProducts $boughtProduct = null, Request $request)
    {
        if ($this->getUser() != $boughtProduct->getUser()) {
            return $this->redirectToRoute("user_profile");
        }

        $productToSell = $boughtProduct->getProduct();
        $price = $request->get('price');
        $quantity = $request->get('quantity');
        $product = clone $productToSell;

        if ($price <= 0 || $quantity <= 0 || $quantity > $boughtProduct->getQuantity()) {
            return $this->redirectToRoute('products_sell_view', ['id' =>$boughtProduct->getId()]);
        }
        $product->setPrice($request->get('price'));
        $product->setQuantity($request->get('quantity'));
        $product->setSeller($this->getUser());

        $boughtProduct->setQuantity($boughtProduct->getQuantity() - $quantity);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        return $this->redirectToRoute("user_profile");
    }

    /**
     * @Route("/products/sell/view/{id}", name="products_sell_view")
     */
    public function sellProductView(BoughtProducts $boughtProduct = null)
    {
        if($boughtProduct == null){
            return $this->redirectToRoute("user_profile");
        }
        if ($this->getUser() != $boughtProduct->getUser()) {
            return $this->redirectToRoute("user_profile");
        }

        return $this->render("product/sell.html.twig", ['product' => $boughtProduct]);
    }
}
