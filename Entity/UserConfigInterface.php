<?php

namespace Agit\UserBundle\Entity;

interface UserConfigInterface
{
    /**
     * expected to return an instance of a default UserConfig
     *
     * @return AbstractUserConfig
     */
    static public function getDefaultConfig();

    public function getId();
}
