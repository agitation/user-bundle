<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Plugin;

use Agit\BaseBundle\Pluggable\Seed\SeedEntry;
use Agit\BaseBundle\Pluggable\Seed\SeedPlugin;
use Agit\BaseBundle\Pluggable\Seed\SeedPluginInterface;
use Agit\IntlBundle\Tool\Translate;

/**
 * @SeedPlugin(entity="AgitUserBundle:UserRole")
 */
class UserRoleSeedPlugin implements SeedPluginInterface
{
    private $data = [];

    public function load()
    {
        $this->data = [];

        $roles = [
            // this is a "super user" role, i.e. has automatically *all* capabilities
            ["id" => "administrator", "name" => Translate::noopX("user role", "Administrator"), "isSuper" => true]
        ];

        foreach ($roles as $role) {
            $seedEntry = new SeedEntry();
            $seedEntry->setDoUpdate(true);
            $seedEntry->setData($role);
            $this->data[] = $seedEntry;
        }
    }

    public function nextSeedEntry()
    {
        return array_pop($this->data);
    }
}
