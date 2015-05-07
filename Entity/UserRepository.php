<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    private $RoleService;

    private $CapabilityService;

    public function setRoleService(RoleService $RoleService)
    {
        $this->RoleService = $RoleService;
    }

    public function setCapabilityService(CapabilityService $CapabilityService)
    {
        $this->CapabilityService = $CapabilityService;
    }

    public function loadUserByUsername($email)
    {
        $User = $this
            ->createQueryBuilder('u')
            ->where('u.email = :email')
            ->andWhere('u.status = ?1')
            ->setParameter(1, 1)
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if (!$User)
            throw new UsernameNotFoundException("No user was found.");

        return $User;
    }

    public function userExists($email)
    {
        return (bool)$this
            ->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function refreshUser(UserInterface $User)
    {
        $class = get_class($User);

        if (!$this->supportsClass($class))
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));

        return $this->find($User->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
}
