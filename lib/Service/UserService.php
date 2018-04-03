<?php
declare(strict_types=1);

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Service;

use Agit\BaseBundle\Service\EntityService;
use Agit\IntlBundle\Tool\Translate;
use Agit\UserBundle\Entity\UserInterface;
use Agit\UserBundle\Entity\UserRole;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class UserService
{
    const AUTOFILL_USER_FIELDS = ['name', 'email'];

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorage
     */
    private $securityTokenStorage;

    /**
     * @var EncoderFactory
     */
    private $securityEncoderFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityService
     */
    private $entityService;

    /**
     * @var PasswordValidator
     */
    private $passwordValidator;

    private $user = false;

    public function __construct(
        SessionInterface $session,
        TokenStorage $securityTokenStorage,
        EncoderFactory $securityEncoderFactory,
        EntityManager $entityManager,
        EntityService $entityService,
        PasswordValidator $passwordValidator
    ) {
        $this->session = $session;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->securityEncoderFactory = $securityEncoderFactory;
        $this->entityManager = $entityManager;
        $this->entityService = $entityService;
        $this->passwordValidator = $passwordValidator;
    }

    public function authenticate($username, $password)
    {
        $user = $this->getUser($username);
        $encoder = $this->securityEncoderFactory->getEncoder($user);

        if (! $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))
        {
            throw new UnauthorizedHttpException(Translate::t('Authentication has failed. Please check your user name and your password.'));
        }

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

    public function getUser($id)
    {
        $field = is_int($id) ? 'id' : 'email';

        $user = $this->entityManager->getRepository(UserInterface::class)
            ->findOneBy([$field => $id]);

        if (! $user || $user->isDeleted())
        {
            throw new NotFoundHttpException('The requested user does not exist.');
        }

        return $user;
    }

    public function userExists($id)
    {
        $exists = false;

        try
        {
            $this->getUser($id);
            $exists = true;
        }
        catch (NotFoundHttpException $e)
        {
        }

        return $exists;
    }

    public function getCurrentUser()
    {
        $user = null;

        if ($this->user === false)
        {
            $token = $this->securityTokenStorage->getToken();
            $user = $token ? $token->getUser() : null;
            $this->user = is_object($user) ? $user : null;
        }

        return $this->user;
    }

    public function currentUserCan($cap)
    {
        $user = $this->getCurrentUser();

        return $user && $user->hasCapability($cap);
    }

    public function createUser(array $fields)
    {
        $user = $this->entityManager->getClassMetadata(UserInterface::class)->newInstance();

        $user->setSalt(base64_encode(random_bytes(18)));
        $this->setPassword($user, base64_encode(random_bytes(18)) . 'Aa1');
        $this->updateUser($user, $fields);

        return $user;
    }

    public function updateUser(UserInterface $user, array $fields)
    {
        $this->setUserFields($user, $fields);
        $this->entityService->validate($user);
    }

    public function setPassword(UserInterface $user, $password)
    {
        $user->setPassword($this->encodePassword($user, $password));
        $user->eraseCredentials();
    }

    public function encodePassword(UserInterface $user, $password)
    {
        $this->passwordValidator->validate($password);
        $encoder = $this->securityEncoderFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }

    private function setUserFields(UserInterface $user, array $fields)
    {
        if (isset($fields['role']))
        {
            $role = $this->entityManager->find(UserRole::class, $fields['role']);
            $user->setRole($role);
            unset($fields['role']);
        }
        else
        {
            $role = $user->getRole();
        }

        if (! $role)
        {
            throw new Exception('Invalid role.');
        }

        if (isset($fields['capabilities']))
        {
            // we will (if necessary) remove the capabilities already present in the role.

            $roleCaps = array_map(function ($roleCap) {
                return $roleCap->getId();
            }, $role->getCapabilities()->getValues());

            $fields['capabilities'] = array_filter($fields['capabilities'], function ($cap) use ($roleCaps) {
                return ! in_array($cap, $roleCaps);
            });
        }

        foreach ($fields['capabilities'] as $cap)
        {
            $user->addCapability($this->entityManager->getReference(UserRole::class, $cap));
        }

        unset($fields['capabilities']);
        $this->entityService->fill($user, $fields, static::AUTOFILL_USER_FIELDS);
    }
}
