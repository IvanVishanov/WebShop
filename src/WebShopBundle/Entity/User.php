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
        $this->cart = [];
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
     * @var Role[]/ArrayCollection
     * @ORM\ManyToMany(targetEntity="WebShopBundle\Entity\Role",inversedBy="users")
     * @ORM\JoinTable(name="user_roles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id",referencedColumnName="id")}
     *      )
     */
    private $roles;

    /**
     * @var Product[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="WebShopBundle\Entity\Product",mappedBy="seller")
     */
    private $products;

    /**
     * @var array
     *
     * @ORM\Column(name="cart",type="array")
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
        return array_map(function (Role $r) {
            return $r->getName();
        }, $this->roles->toArray());
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
     * @return array
     */
    public function getCart(): array
    {
        return $this->cart;
    }

    /**
     * @param array $cart
     */
    public function setCart(array $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @param Product $product
     */
    public function addCart(Product $product){
        $this->cart[] = $product;
    }

}

