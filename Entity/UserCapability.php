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
use Symfony\Component\Validator\Constraints as Assert;
use Agit\BaseBundle\Entity\IdentityAwareTrait;
use Agit\IntlBundle\Translate;

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return Translate::x("user capability", $this->name);
    }
}
