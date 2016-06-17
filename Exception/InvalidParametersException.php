<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Exception;

use Agit\CommonBundle\Exception\AgitException;

/**
 * The parameters passed for creating or modifying a user are invalid.
 */
class InvalidParametersException extends AgitException
{
    protected $httpStatus = 400;
}
