<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Exception;

use Agit\BaseBundle\Exception\PublicException;

/**
 * The authentication information is invalid. This counts as a “bad request”,
 * hence the 400 status code.
 */
class AuthenticationFailedException extends PublicException
{
    protected $statusCode = 400;
}
