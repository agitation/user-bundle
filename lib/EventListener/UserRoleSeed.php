<?php
declare(strict_types=1);
/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\EventListener;

use Agit\IntlBundle\Tool\Translate;
use Agit\SeedBundle\Event\SeedEvent;

class UserRoleSeed
{
    public function registerSeed(SeedEvent $event)
    {
        $event->addSeedEntry('AgitUserBundle:UserRole', [
            'id' => 'administrator',
            'name' => Translate::noopX('user role', 'Administrator'),
            'isSuper' => true
        ]);
    }
}
