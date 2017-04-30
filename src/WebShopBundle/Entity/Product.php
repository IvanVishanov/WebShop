<?php

namespace WebShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="WebShopBundle\Repository\ProductRepository")
 */
class Product
{
    function __construct()
    {
        $this->quantities = new ArrayCollection();
        $this->boughtProducts = new ArrayCollection();
    }

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="decimal", precision=11, scale=2)
     */
    private $price;

    /**
     * @var int
     * @Assert\NotBlank()
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     *
     * @ORM\Column(name="image", type="string")
     * @Assert\File(
     *     maxSize="10M",
     *     mimeTypes={ "image/x-icon" ,"image/png","image/jpeg"})
     */
    private $image;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="WebShopBundle\Entity\Category",inversedBy="products")
     * @ORM\JoinColumn(name="category_id",referencedColumnName="id")
     */
    private $category;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="WebShopBundle\Entity\User",inversedBy="products")
     * @ORM\JoinColumn(name="seller_id",referencedColumnName="id")
     */
    private $seller;

    /**
     * @ORM\OneToMany(targetEntity="WebShopBundle\Entity\Quantity", mappedBy = "product" )
     */
    private $quantities;

    /**
     * @ORM\OneToMany(targetEntity="WebShopBundle\Entity\BoughtProducts", mappedBy = "product" )
     */
    private $boughtProducts;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean", nullable=true)
     */
    private $deleted = false;

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
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Product
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return User
     */
    public function getSeller(): User
    {
        return $this->seller;
    }

    /**
     * @param User $seller
     */
    public function setSeller(User $seller)
    {
        $this->seller = $seller;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getQuantities()
    {
        return $this->quantities;
    }

    public function addQuantity($quantity)
    {
        if (!$this->quantities->contains($quantity)) {
            $this->quantities->add($quantity);
            $quantity->setProduct($this);
        }

        return $this;
    }


    /**
     * @return mixed
     */
    public function getBoughtProducts()
    {
        return $this->boughtProducts;
    }


    public function addBoughtProduct(BoughtProducts $job)
    {
        if (!$this->boughtProducts->contains($job)) {
            $this->boughtProducts->add($job);
            $job->setProduct($this);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

}

