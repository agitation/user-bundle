<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Agit\BaseBundle\Entity\IdentityAwareTrait;
use Agit\IntlBundle\Tool\Translate;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserCapability
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return Translate::x("user capability", $this->name);
    }
}
