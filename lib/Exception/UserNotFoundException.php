<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Exception;

use Agit\BaseBundle\Exception\AgitException;

/**
 * The requested user could not be found.
 */
class UserNotFoundException extends AgitException
{
    protected $httpStatus = 404;
}
