<?php

namespace WebShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="WebShopBundle\Repository\CartRepository")
 */
class Cart
{
    function __construct()
    {
        $this->quantities = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * One Cart has One Customer.
     * @ORM\OneToOne(targetEntity="WebShopBundle\Entity\User", mappedBy="cart")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="WebShopBundle\Entity\Quantity", mappedBy="cart", cascade={"persist"}, orphanRemoval=TRUE)
     */
    private $quantities;

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

    /**
     * @return mixed
     */
    public function getQuantities()
    {
        return $this->quantities;
    }

    public function getTotal()
    {
        $total = 0;
        foreach($this->quantities as $quantity){
            $total += $quantity->getTotal();
        }
        return $total;
    }

    public function addQuantity(Quantity $job)
    {
        if (!$this->quantities->contains($job)) {
            $this->quantities->add($job);
            $job->setCart($this);
        }

        return $this;
    }

    public function removeQuantity(Quantity $job)
    {
        if ($this->quantities->contains($job)) {
            $this->quantities->removeElement($job);
            $job->setCart(null);
        }

        return $this;
    }

    public function getProducts()
    {
        return array_map(
            function ($quantity) {
                return $quantity->getProduct();
            },
            $this->quantities->toArray()
        );
    }

    /**
     * @param mixed $quantities
     */
    public function setQuantities($quantities)
    {
        $this->quantities = $quantities;
    }
}

