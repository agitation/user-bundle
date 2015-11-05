<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Plugin;

use Agit\IntlBundle\Translate;
use Agit\PluggableBundle\Strategy\Seed\SeedPluginInterface;
use Agit\PluggableBundle\Strategy\Seed\SeedPlugin;
use Agit\PluggableBundle\Strategy\Seed\SeedEntry;

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
            [ 'id' => 'administrator', 'name' => Translate::noopX('Administrator', 'user role'), 'isSuper' => true ],

            // this is a fallback role; no capabilities should be assigned to this role
            [ 'id' => 'member', 'name' => Translate::noopX('Member', 'user role'), 'isSuper' => false ]
        ];

        foreach ($roles as $role)
        {
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