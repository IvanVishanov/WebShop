<?php

namespace WebShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="WebShopBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->boughtProducts = new ArrayCollection();
        $this->setCart(new Cart());
        $this->setCash(2000);

    }

    public function __toString()
    {
        return $this->getUsername();
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
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=100, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="cash", type="decimal", precision=11, scale=2)
     */
    private $cash;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var Role[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="WebShopBundle\Entity\Role",inversedBy="users")
     * @ORM\JoinTable(name="user_roles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id",referencedColumnName="id")}
     *      )
     */
    private $roles;

    /**
     * @var Cart
     * @ORM\OneToOne(targetEntity="WebShopBundle\Entity\Cart",cascade={"persist"},inversedBy="user")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    private $cart;

    /**
     * @var Product[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="WebShopBundle\Entity\Product",mappedBy="seller")
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="WebShopBundle\Entity\BoughtProducts", mappedBy="user", cascade={"persist"}, orphanRemoval=TRUE)
     */
    private $boughtProducts;

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        $stringRoles = [];
        foreach ($this->roles as $role) {

            $stringRoles[] = is_string($role) ? $role:$role->getRole();
        }

        return $stringRoles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function addRole($userRole)
    {
        $this->roles[] = $userRole;
    }

    /**
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @param Cart $cart
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return string
     */
    public function getCash()
    {
        return $this->cash;
    }

    /**
     * @param string $cash
     */
    public function setCash($cash)
    {
        $this->cash = $cash;
    }


    /**
     * @param mixed $boughtProducts
     */
    public function setBoughtProducts($boughtProducts)
    {
        $this->boughtProducts = $boughtProducts;
    }

    public function addBoughtProduct(BoughtProducts $job)
    {
        if (!$this->boughtProducts->contains($job)) {
            $this->boughtProducts->add($job);
            $job->setUser($this);
        }

        return $this;
    }

    public function removeBoughtProduct(BoughtProducts $boughtProducts)
    {
        if ($this->boughtProducts->contains($boughtProducts)) {
            $this->boughtProducts->removeElement($boughtProducts);
            $boughtProducts->setUser(null);
        }

        return $this;
    }

    public function getBoughtProduct()
    {
        return $this->boughtProducts;
    }

    public function getBoughtProducts()
    {
        return array_map(
            function ($product) {
                return $product->getProduct();
            },
            $this->boughtProducts->toArray()
        );
    }

    /**
     * @param ArrayCollection|Role[] $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

}

