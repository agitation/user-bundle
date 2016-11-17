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
 * The parameters passed for creating or modifying a user are invalid.
 */
class InvalidParametersException extends AgitException
{
    protected $statusCode = 400;
}
