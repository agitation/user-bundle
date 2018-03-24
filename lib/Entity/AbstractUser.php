<?php
declare(strict_types=1);

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Agit\BaseBundle\Entity\DeletableInterface;
use Agit\BaseBundle\Entity\DeletableTrait;
use Agit\BaseBundle\Entity\GeneratedIdentityAwareTrait;
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
     *
     * NOTE: This field must remain UNIQUE and NULLABLE, because it also serves
     * as user login. For use cases where different user entities may have the
     * same e-mail address, this field should be left empty and another e-mail
     * field should be introduced.
     */
    private $email;

    /**
     * @ORM\Column(name="salt",type="string",length=40)
     * @Assert\Length(min=20)
     */
    private $salt;

    /**
     * @ORM\Column(name="password",type="string",length=90)
     * @Assert\Length(min=30)
     */
    private $password;

    public function equals(UserInterface $user)
    {
        return $user->getId() === $this->getId() && get_class($user) === get_class($this);
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
        return ['user'];
    }

    public function eraseCredentials()
    {
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
}
