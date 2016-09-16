<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Agit\BaseBundle\Entity\GeneratedIdentityAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractUserConfig implements UserConfigInterface
{
    use GeneratedIdentityAwareTrait;

    /**
     * @ORM\OneToOne(targetEntity="\Agit\UserBundle\Entity\User", inversedBy="userConfig")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotNull()
     */
    protected $user;

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return UserConfig
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
