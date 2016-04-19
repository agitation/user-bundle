<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Security\Core\User\UserInterface;
use Agit\CommonBundle\Entity\GeneratedIdentityAwareTrait;
use Agit\CommonBundle\Exception\InternalErrorException;

/**
 * @ORM\Entity(repositoryClass="Agit\UserBundle\Entity\UserRepository")
 * @ORM\Table(indexes={@ORM\Index(name="idx_email_active",columns={"email","active"})})
 */
class User implements UserInterface
{
    use GeneratedIdentityAwareTrait;

    /**
     * @ORM\Column(type="string",length=70)
     */
    private $name;

    /**
     * @ORM\Column(type="string",length=70,unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(name="salt",type="string",length=40)
     */
    private $salt;

    /**
     * @ORM\Column(name="password",type="string",length=88)
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity="UserRole")
     */
    private $role;

    /**
     * Extra capabilities, i.e. which are not part of the user's Role
     *
     * @ORM\ManyToMany(targetEntity="UserCapability")
     */
    private $capabilities = [];

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->password = sha1(uniqid(mt_rand(), true));
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
        // stupidity fallback: make sure it is some sort of a hash
        if (strlen($password) < 30)
            throw new InternalErrorException("Password must be encrypted!");

        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles() // the one required by Symfony, useless for us
    {
        return ['user'];
    }

    public function eraseCredentials()
    {

    }

    public function hasRole($role)
    {
        return ($role === $this->getRole()->getId());
    }

    public function hasCapability($cap)
    {
        $has = false;

        if ($this->getRole()->isSuper() || $this->getRole()->hasCapability($cap))
        {
            $has = true;
        }
        else
        {
            foreach ($this->getCapabilities()->getValues() as $capability)
            {
                if ($capability->getId() === $cap)
                {
                    $has = true;
                    break;
                }
            }
        }

        return $has;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * Set active
     *
     * @param smallint $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return smallint
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * is active
     *
     * @return smallint
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Set Role
     *
     * @param UserRole $role
     * @return User
     */
    public function setRole(UserRole $role = null)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Get Role
     *
     * @return UserRole
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Add Capability
     *
     * @param UserCapability $capability
     * @return User
     */
    public function addCapability(UserCapability $capability)
    {
        $this->capabilities[] = $capability;
        return $this;
    }

    /**
     * Get Capabilities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }
}
