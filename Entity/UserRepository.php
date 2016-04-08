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
    public function loadUserByUsername($email)
    {
        $user = $this
            ->createQueryBuilder('u')
            ->where('u.email = :email')
            ->andWhere('u.status = ?1')
            ->setParameter(1, 1)
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if (!$user)
            throw new UsernameNotFoundException("No user was found.");

        return $user;
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

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);

        if (!$this->supportsClass($class))
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
}
