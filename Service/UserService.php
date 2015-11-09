<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Service;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Doctrine\ORM\EntityManager;
use Agit\CoreBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Translate;
use Agit\ValidationBundle\Service\ValidationService;
use Agit\UserBundle\Exception\UnauthorizedException;
use Agit\UserBundle\Entity\User;

class UserService
{
    private $securityContext;

    private $securityEncoderFactory;

    private $entityManager;

    private $validationService;

    private $user = false;

    public function __construct(SecurityContext $securityContext, EncoderFactory $securityEncoderFactory, EntityManager $entityManager, ValidationService $validationService)
    {
        $this->securityContext = $securityContext;
        $this->securityEncoderFactory = $securityEncoderFactory;
        $this->entityManager = $entityManager;
        $this->validationService = $validationService;
    }

    public function authenticate($username, $password)
    {
        if (!$this->validationService->isValid('email', $username))
            throw new UnauthorizedException(Translate::t("Authentication has failed. Please check your user name and your password."));

        $user = $this->entityManager->getRepository('AgitUserBundle:User')
            ->findOneBy(['email' => $username, 'active' => true]);

        if (!$user)
            throw new UnauthorizedException(Translate::t("Authentication has failed. Please check your user name and your password."));

        $password = $this->securityEncoderFactory->getEncoder($user)
            ->encodePassword($password, $user->getSalt());

        if ($password !== $user->getPassword())
            throw new UnauthorizedException(Translate::t("Authentication has failed. Please check your user name and your password."));

        $this->setCurrentUser($user);
    }

    public function getCurrentUser()
    {
        $user = null;

        if ($this->user === false)
        {
            $securityToken = $this->securityContext->getToken();

            if (is_object($securityToken) && is_callable([$securityToken, 'getUser']))
                $user = $securityToken->getUser();

            $this->user = ($user instanceof User) ? $user : null;
        }

        return $this->user;
    }

    public function currentUserCan($cap)
    {
        $can = false;
        $user = $this->getCurrentUser();

        if ($user)
            $can = $user->hasCapability($cap);

        return $can;
    }
}
