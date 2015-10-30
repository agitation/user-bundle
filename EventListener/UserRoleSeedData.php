<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Agit\CoreBundle\Pluggable\Strategy\Seed\SeedRegistrationEvent;
use Agit\IntlBundle\Service\Translate;

class UserRoleSeedData
{
    public function onRegistration(SeedRegistrationEvent $registrationEvent)
    {
        $translate = new Translate();

        $roles = [
            ['administrator', $translate->noopX('Administrator', 'user role'), true], // super user
            ['member', $translate->noopX('Member', 'user role'), false], // should not have any capabilities, just a fallback role
        ];

        foreach ($roles as $role)
        {
            $registrationData = $registrationEvent->createContainer();

            $registrationData->setData([
                'id' => $role[0],
                'name' => $role[1],
                'isSuper' => $role[2]
            ]);

            $registrationEvent->register($registrationData);
        }
    }
}
