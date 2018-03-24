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
 * The requested user could not be found.
 */
class UserNotFoundException extends PublicException
{
    protected $statusCode = 404;
}
