<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Service;

use Agit\CommonBundle\Helper\StringHelper;
use Agit\UserBundle\Exception\InvalidParametersException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Doctrine\ORM\EntityManager;
use Agit\IntlBundle\Translate;
use Agit\ValidationBundle\Service\ValidationService;
use Agit\UserBundle\Exception\UnauthorizedException;
use Agit\UserBundle\Entity\User;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Constraints\Valid;

class UserService
{
    private $session;

    private $securityTokenStorage;

    private $securityEncoderFactory;

    private $entityManager;

    private $entityValidator;

    private $validationService;

    private $user = false;

    public function __construct
    (
        SessionInterface $session,
        TokenStorage $securityTokenStorage,
        EncoderFactory $securityEncoderFactory,
        EntityManager $entityManager,
        RecursiveValidator $entityValidator,
        ValidationService $validationService
    )
    {
        $this->session = $session;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->securityEncoderFactory = $securityEncoderFactory;
        $this->entityManager = $entityManager;
        $this->entityValidator = $entityValidator;
        $this->validationService = $validationService;
    }

    public function authenticate($username, $password)
    {
        if (!$this->validationService->isValid("email", $username))
            throw new UnauthorizedException(Translate::t("Authentication has failed. Please check your user name and your password."));

        $user = $this->entityManager->getRepository("AgitUserBundle:User")
            ->findOneBy(["email" => $username, "active" => true]);

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
        $token = new UsernamePasswordToken($this->user, null, "agitation", $this->user->getRoles());
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
            $user = $this->securityTokenStorage->getToken()->getUser();
            $this->user = ($user instanceof User) ? $user : null;
        }

        return $this->user;
    }

    public function currentUserCan($cap)
    {
        $user = $this->getCurrentUser();
        return ($user && $user->hasCapability($cap));
    }

    public function createUser($name, $email, $role = null, $active = true)
    {
        // find out which class implements UserConfig
        $userConfigMetadata = $this->entityManager->getClassMetadata("Agit\UserBundle\Entity\UserConfigInterface");
        $userConfigClass = $userConfigMetadata->name;

        $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
        $iv = mcrypt_create_iv($ivSize,  MCRYPT_RAND);
        $salt = sha1(microtime(true) . $iv);
        $randPass = StringHelper::createRandomString(20);

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setRole($role ? $this->entityManager->getReference("AgitUserBundle:UserRole", $role) : null);
        $user->setActive($active);
        $user->setSalt($salt);
        $user->setConfig($userConfigClass::getDefaultConfig());
        $user->getConfig()->setUser($user);

        $this->setPassword($user, $randPass);

        $errors = $this->entityValidator->validate($user, new Valid(["traverse" => true, "deep" => true]));

        if (count($errors) > 0)
            throw new InvalidParametersException((string)$errors);

        return $user;
    }

    public function setPassword(User $user, $password)
    {
        $this->validationService->validate("password", $password);

        $encoder = $this->securityEncoderFactory->getEncoder($user);
        $pwdHash = $encoder->encodePassword($password, $user->getSalt());

        $user->setPassword($pwdHash);
        $user->eraseCredentials();
    }
}
