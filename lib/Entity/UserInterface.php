<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Agit\BaseBundle\Entity\IdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface, IdentityInterface
{
}
