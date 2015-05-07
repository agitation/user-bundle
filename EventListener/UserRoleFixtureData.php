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
use Agit\CoreBundle\Pluggable\Strategy\Fixture\FixtureRegistrationEvent;
use Agit\IntlBundle\Service\Translate;

class UserRoleFixtureData
{
    public function onRegistration(FixtureRegistrationEvent $RegistrationEvent)
    {
        $Translate = new Translate();

        $roles = [
            ['administrator', $Translate->noopX('Administrator', 'user role'), true], // super user
            ['member', $Translate->noopX('Member', 'user role'), false], // should not have any capabilities, just a fallback role
        ];

        foreach ($roles as $role)
        {
            $RegistrationData = $RegistrationEvent->createContainer();

            $RegistrationData->setData([
                'id' => $role[0],
                'name' => $role[1],
                'isSuper' => $role[2]
            ]);

            $RegistrationEvent->register($RegistrationData);
        }
    }
}
