<?php

namespace WebShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BoughtProducts
 *
 * @ORM\Table(name="bought_products")
 * @ORM\Entity(repositoryClass="WebShopBundle\Repository\BoughtProductsRepository")
 */
class BoughtProducts
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
     * @ORM\ManyToOne(targetEntity="WebShopBundle\Entity\User", inversedBy="boughtProducts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=FALSE)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="WebShopBundle\Entity\Product", inversedBy="boughtProducts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=FALSE)
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set quantity
     *
     * @param string $quantity
     *
     * @return BoughtProducts
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
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

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }


}

