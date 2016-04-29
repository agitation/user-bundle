<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Doctrine\ORM\EntityManager;
use Agit\IntlBundle\Translate;
use Agit\ValidationBundle\Service\ValidationService;
use Agit\UserBundle\Exception\UnauthorizedException;
use Agit\UserBundle\Entity\User;

class UserService
{
    private $session;

    private $securityTokenStorage;

    private $securityEncoderFactory;

    private $entityManager;

    private $validationService;

    private $user = false;

    public function __construct
    (
        SessionInterface $session,
        TokenStorage $securityTokenStorage,
        EncoderFactory $securityEncoderFactory,
        EntityManager $entityManager,
        ValidationService $validationService
    )
    {
        $this->session = $session;
        $this->securityTokenStorage = $securityTokenStorage;
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

        $encoder = $this->securityEncoderFactory->getEncoder($user);

        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))
            throw new UnauthorizedException(Translate::t("Authentication has failed. Please check your user name and your password."));

        $this->user = $user;
    }

    public function login($username, $password)
    {
        $this->authenticate($username, $password);
        $token = new UsernamePasswordToken($this->user, null, 'agitation', $this->user->getRoles());
        $this->securityTokenStorage->setToken($token);
    }

    public function logout()
    {
        $this->user = null;
        $this->securityTokenStorage->setToken(null);
        $this->session->invalidate();
    }

    public function getCurrentUser()
    {
        $user = null;

        if ($this->user === false)
        {
            $securityToken = $this->securityTokenStorage->getToken();

            if (is_object($securityToken) && is_callable([$securityToken, 'getUser']))
                $user = $securityToken->getUser();

            $this->user = ($user instanceof User) ? $user : null;
        }

        return $this->user;
    }

    public function currentUserCan($cap)
    {
        $user = $this->getCurrentUser();
        return ($user && $user->hasCapability($cap));
    }
}
