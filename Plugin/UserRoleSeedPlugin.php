<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Plugin;

use Agit\BaseBundle\Tool\Translate;
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
            [ "id" => "administrator", "name" => Translate::noopX("user role", "Administrator"), "isSuper" => true ]
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
