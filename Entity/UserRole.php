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
use Agit\CoreBundle\Entity\AbstractEntity;
use Agit\CoreBundle\Exception\InternalErrorException;

/**
 * @ORM\Entity
 */
class UserRole extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string",length=35,unique=true)
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
    private $capabilityList = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->capabilityList = new ArrayCollection();
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
            foreach ($this->getCapabilityList()->getValues() as $capability)
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
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->translate->t($this->name);
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
    public function getCapabilityList()
    {
        return $this->capabilityList;
    }
}