<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Service;

use Agit\BaseBundle\Tool\StringHelper;
use Agit\IntlBundle\Tool\Translate;
use Agit\UserBundle\Entity\UserInterface;
use Agit\UserBundle\Exception\AuthenticationFailedException;
use Agit\UserBundle\Exception\InvalidParametersException;
use Agit\UserBundle\Exception\UserNotFoundException;
use Agit\ValidationBundle\ValidationService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    private $session;

    private $securityTokenStorage;

    private $securityEncoderFactory;

    private $entityManager;

    private $entityValidator;

    private $validationService;

    private $user = false;

    public function __construct(
        SessionInterface $session,
        TokenStorage $securityTokenStorage,
        EncoderFactory $securityEncoderFactory,
        EntityManager $entityManager,
        ValidatorInterface $entityValidator,
        ValidationService $validationService
    ) {
        $this->session = $session;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->securityEncoderFactory = $securityEncoderFactory;
        $this->entityManager = $entityManager;
        $this->entityValidator = $entityValidator;
        $this->validationService = $validationService;
    }

    public function authenticate($username, $password)
    {
        if (! $this->validationService->isValid("email", $username)) {
            throw new AuthenticationFailedException(Translate::t("Authentication has failed. Please check your user name and your password."));
        }

        $user = $this->entityManager->getRepository("AgitUserBundle:UserInterface")
            ->findOneBy(["email" => $username, "deleted" => false]);

        if (! $user) {
            throw new AuthenticationFailedException(Translate::t("Authentication has failed. Please check your user name and your password."));
        }

        $encoder = $this->securityEncoderFactory->getEncoder($user);

        if (! $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            throw new AuthenticationFailedException(Translate::t("Authentication has failed. Please check your user name and your password."));
        }

        $this->user = $user;
    }

    public function login($username, $password)
    {
        $this->authenticate($username, $password);
        $token = new UsernamePasswordToken($this->user, null, "agitation", $this->user->getRoles());
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
        $field = is_int($id) ? "id" : "email";

        $user = $this->entityManager
            ->getRepository("AgitUserBundle:UserInterface")
            ->findOneBy([$field => $id, "deleted" => 0]);

        if (! $user) {
            throw new UserNotFoundException(Translate::t("The requested user does not exist."));
        }

        return $user;
    }

    public function getCurrentUser()
    {
        $user = null;

        if ($this->user === false) {
            $user = $this->securityTokenStorage->getToken()->getUser();
            $this->user = ($user instanceof UserInterface) ? $user : null;
        }

        return $this->user;
    }

    public function currentUserCan($cap)
    {
        $user = $this->getCurrentUser();

        return $user && $user->hasCapability($cap);
    }

    public function createUser($name, $email, $role = null, $active = true)
    {
        $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
        $iv = mcrypt_create_iv($ivSize,  MCRYPT_RAND);
        $salt = sha1(microtime(true) . $iv);
        $randPass = StringHelper::createRandomString(15) . "Aa1"; // suffix needed to ensure PW policy compliance

        // find out which class implements the User entity
        $userMetadata = $this->entityManager->getClassMetadata("AgitUserBundle:UserInterface");
        $userClass = $userMetadata->name;

        $user = new $userClass();
        $user->setName($name);
        $user->setEmail($email);
        $user->setRole($role ? $this->entityManager->getReference("AgitUserBundle:UserRole", $role) : null);
        $user->setDeleted(! $active);
        $user->setSalt($salt);
        $this->setPassword($user, $randPass);

        $errors = $this->entityValidator->validate($user, new Valid(["traverse" => true]));

        if (count($errors) > 0) {
            throw new InvalidParametersException((string) $errors);
        }

        return $user;
    }

    public function setPassword(UserInterface $user, $password)
    {
        $user->setPassword($this->encodePassword($user, $password));
        $user->eraseCredentials();
    }

    public function encodePassword(UserInterface $user, $password)
    {
        $this->validationService->validate("password", $password);
        $encoder = $this->securityEncoderFactory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }
}
