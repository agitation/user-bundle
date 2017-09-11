<?php
declare(strict_types=1);
/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

/**
 * Technically, this is not different from the UserInterface,
 * but this one should be used for users which implement the
 * primary authentication mechanism.
 */
interface PrimaryUserInterface extends UserInterface
{
}
