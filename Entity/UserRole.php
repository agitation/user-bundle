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
use Agit\CommonBundle\Entity\IdentityAwareTrait;
use Agit\CommonBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;

/**
 * @ORM\Entity
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
     * Constructor
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return Translate::x($this->name, 'user role');
    }

    /**
     * Get isSuper
     *
     * @return boolean
     */
    public function isSuper()
    {
        return $this->isSuper;
    }

    /**
     * Get Capability
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }
}
