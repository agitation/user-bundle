<?php
declare(strict_types=1);

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Agit\BaseBundle\Entity\IdentityAwareTrait;
use Agit\IntlBundle\Tool\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class UserRole
{
    use IdentityAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="string",length=35)
     */
    private $id;

    /**
     * @ORM\Column(type="string",length=70)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSuper;

    /**
     * @ORM\ManyToMany(targetEntity="UserCapability")
     */
    private $capabilities = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->capabilities = new ArrayCollection();
    }

    public function hasCapability($cap)
    {
        $has = false;

        if ($this->isSuper())
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return Translate::x('user role', $this->name);
    }

    /**
     * Get isSuper.
     *
     * @return bool
     */
    public function isSuper()
    {
        return $this->isSuper;
    }

    /**
     * Get Capability.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }
}
