<?php
declare(strict_types=1);
/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Exception;

use Agit\BaseBundle\Exception\PublicException;

/**
 * The parameters passed for creating or modifying a user are invalid.
 */
class InvalidParametersException extends PublicException
{
    protected $statusCode = 400;
}
