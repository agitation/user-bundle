<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Agit\BaseBundle\Entity\DeletableTrait;
use Agit\BaseBundle\Entity\DeletableInterface;
use Agit\BaseBundle\Entity\GeneratedIdentityAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractUser implements UserInterface, DeletableInterface
{
    use GeneratedIdentityAwareTrait;
    use DeletableTrait;

    /**
     * @ORM\Column(type="string",length=70)
     * @Assert\Length(min=5)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=70, nullable=true, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="salt",type="string",length=40)
     * @Assert\Length(min=20)
     */
    private $salt;

    /**
     * @ORM\Column(name="password",type="string",length=88)
     * @Assert\Length(min=30)
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity="UserRole")
     * @Assert\Valid
     */
    private $role;

    /**
     * Extra capabilities, i.e. which are not part of the user’s Role.
     *
     * @ORM\ManyToMany(targetEntity="UserCapability")
     */
    private $capabilities = [];

    public function __construct()
    {
        $this->capabilities = new ArrayCollection();
    }

    public function equals(UserInterface $user)
    {
        return $user->getId() === $this->getId();
    }

    public function getUsername()
    {
        return $this->getEmail();
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles() // the one required by Symfony, useless for us
    {
        return ["user"];
    }

    public function eraseCredentials()
    {
    }

    public function hasRole($role)
    {
        return $this->role && $role === $this->role->getId();
    }

    public function hasCapability($cap)
    {
        $has = false;

        if ($this->role && ($this->role->isSuper() || $this->role->hasCapability($cap))) {
            $has = true;
        } else {
            foreach ($this->getCapabilities()->getValues() as $capability) {
                if ($capability->getId() === $cap) {
                    $has = true;
                    break;
                }
            }
        }

        return $has;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * is active.
     *
     * @return smallint
     */
    public function isActive()
    {
        return ! $this->deleted;
    }

    /**
     * Set salt.
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Set Role.
     *
     * @param UserRole $role
     *
     * @return User
     */
    public function setRole(UserRole $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get Role.
     *
     * @return UserRole
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add Capability.
     *
     * @param UserCapability $capability
     *
     * @return User
     */
    public function addCapability(UserCapability $capability)
    {
        $this->capabilities[] = $capability;

        return $this;
    }

    /**
     * Get Capabilities.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }
}