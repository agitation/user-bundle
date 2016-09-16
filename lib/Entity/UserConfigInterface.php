<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

interface UserConfigInterface
{
    /**
     * expected to return an instance of a default UserConfig.
     *
     * @return AbstractUserConfig
     */
    public static function getDefaultConfig();

    public function getId();
}
