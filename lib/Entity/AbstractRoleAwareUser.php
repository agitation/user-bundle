<?php
declare(strict_types=1);
/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractRoleAwareUser extends AbstractUser implements RoleAwareUserInterface
{
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

    public function hasRole($role)
    {
        return $this->role && $role === $this->role->getId();
    }

    public function hasCapability($cap)
    {
        $has = false;

        if ($this->role && ($this->role->isSuper() || $this->role->hasCapability($cap)))
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
     * @return Collection
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }
}
