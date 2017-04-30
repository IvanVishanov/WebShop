<?php

namespace WebShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Quantity
 *
 * @ORM\Table(name="quantity")
 * @ORM\Entity(repositoryClass="WebShopBundle\Repository\QuantityRepository")
 */
class Quantity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="WebShopBundle\Entity\Product", inversedBy="quantities")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=FALSE)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="WebShopBundle\Entity\Cart", inversedBy="quantities")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id", nullable=FALSE)
     */
    private $cart;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTotal()
    {
        return $this->quantity * $this->getProduct()->getPrice();
    }


    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param mixed $cart
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }
}

