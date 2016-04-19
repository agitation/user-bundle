<?php

namespace Agit\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Agit\CommonBundle\Entity\GeneratedIdentityAwareTrait;

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
     * Set user
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
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
