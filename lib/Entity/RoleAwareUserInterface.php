<?php
declare(strict_types=1);

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

interface RoleAwareUserInterface extends UserInterface
{
    public function hasRole($role);

    public function setRole(UserRole $role = null);

    public function getRole();

    public function hasCapability($cap);

    public function addCapability(UserCapability $capability);

    public function getCapabilities();
}
