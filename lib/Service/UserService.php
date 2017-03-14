<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Service;

use Agit\BaseBundle\Entity\DeletableInterface;
use Agit\BaseBundle\Tool\StringHelper;
use Agit\IntlBundle\Tool\Translate;
use Agit\UserBundle\Entity\PrimaryUserInterface;
use Agit\UserBundle\Entity\RoleAwareUserInterface;
use Agit\UserBundle\Entity\UserInterface;
use Agit\UserBundle\Exception\AuthenticationFailedException;
use Agit\UserBundle\Exception\InvalidParametersException;
use Agit\UserBundle\Exception\UserNotFoundException;
use Agit\ValidationBundle\ValidationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    const DEFAULT_USER_ENTITY_CLASS = "AgitUserBundle:PrimaryUserInterface";

    const SPECIAL_USER_ENTITY_FIELDS = ["id", "salt", "password"];

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

    public function authenticate($username, $password, $entityClass = self::DEFAULT_USER_ENTITY_CLASS)
    {
        if (! $this->validationService->isValid("email", $username)) {
            throw new AuthenticationFailedException(Translate::t("Authentication has failed. Please check your user name and your password."));
        }

        $user = $this->getUser($username, $entityClass);
        $encoder = $this->securityEncoderFactory->getEncoder($user);

        if (! $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            throw new AuthenticationFailedException(Translate::t("Authentication has failed. Please check your user name and your password."));
        }

        $this->user = $user;
    }

    public function login($username, $password, $entityClass = self::DEFAULT_USER_ENTITY_CLASS)
    {
        $this->authenticate($username, $password, $entityClass);
        $token = new UsernamePasswordToken($this->user, null, "agitation", $this->user->getRoles());
        $this->securityTokenStorage->setToken($token);
    }

    public function logout()
    {
        $this->user = null;
        $this->securityTokenStorage->setToken(null);
        $this->session->invalidate();
    }

    public function getUser($id, $entityClass = self::DEFAULT_USER_ENTITY_CLASS)
    {
        $field = is_int($id) ? "id" : "email";

        $user = $this->entityManager->getRepository($entityClass)
            ->findOneBy([$field => $id]);

        if (! $user || ! ($user instanceof UserInterface) || ($user instanceof DeletableInterface && $user->isDeleted())) {
            throw new UserNotFoundException(Translate::t("The requested user does not exist."));
        }

        return $user;
    }

    public function getCurrentUser()
    {
        $user = null;

        if ($this->user === false) {
            $token = $this->securityTokenStorage->getToken();
            $user = $token ? $token->getUser() : null;
            $this->user = ($user instanceof PrimaryUserInterface) ? $user : null;
        }

        return $this->user;
    }

    public function currentUserCan($cap)
    {
        $user = $this->getCurrentUser();

        return $user && $user instanceof RoleAwareUserInterface && $user->hasCapability($cap);
    }

    public function createUser(array $fields, $entityClass = self::DEFAULT_USER_ENTITY_CLASS)
    {
        $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
        $iv = mcrypt_create_iv($ivSize,  MCRYPT_RAND);
        $salt = sha1(microtime(true) . $iv);
        $randPass = StringHelper::createRandomString(15) . "Aa1"; // suffix needed to ensure PW policy compliance

        $userClass = $this->entityManager->getClassMetadata($entityClass)->name;
        $user = new $userClass();

        if (! ($user instanceof UserInterface)) {
            throw new InvalidParametersException(sprintf("Invalid user class: %s", $userClass));
        }

        $user->setSalt($salt);
        $this->setPassword($user, $randPass);

        $this->setUserFields($user, $fields);
        $this->validateUser($user);

        return $user;
    }

    public function updateUser(UserInterface $user, array $fields)
    {
        $this->setUserFields($user, $fields);
        $this->validateUser($user);
    }

    public function validateUser(UserInterface $user)
    {
        $errors = $this->entityValidator->validate($user, new Valid(["traverse" => true]));

        if (count($errors) > 0) {
            throw new InvalidParametersException((string) $errors);
        }
    }

    public function setUserFields(UserInterface $user, array $fields)
    {
        // if capabilities are being set, we will (if necessary) remove the ones
        // already present in the role.

        if (isset($fields["capabilities"]) && isset($fields["capabilities"])) {
            if (isset($fields["role"])) {
                $role = $this->entityManager->find("AgitUserBundle:UserRole", $fields["role"]);
            } elseif ($user instanceof RoleAwareUserInterface) {
                $role = $user->getRole();
            }

            if (! $role) {
                throw new InvalidParametersException("Invalid role.");
            }

            $roleCaps = array_map(function ($roleCap) { return $roleCap->getId(); }, $role->getCapabilities()->getValues());

            $fields["capabilities"] = array_filter($fields["capabilities"], function ($cap) use ($roleCaps) {
                return ! in_array($cap, $roleCaps);
            });
        }

        foreach ($fields as $field => $value) {
            if (in_array($field, self::SPECIAL_USER_ENTITY_FIELDS)) {
                throw new InvalidParametersException(sprintf("The `%s` field cannot be updated with this method.", $field));
            }

            $entityMeta = $this->entityManager->getClassMetadata(get_class($user));

            if ($entityMeta->hasField($field)) {
                $entityMeta->setFieldValue($user, $field, $value);
            } elseif ($entityMeta->hasAssociation($field)) {
                $mapping = $entityMeta->getAssociationMapping($field);
                $targetEntity = $mapping["targetEntity"];

                if ($mapping["type"] & ClassMetadataInfo::TO_ONE && is_scalar($value)) {
                    $entityMeta->setFieldValue($user, $field, $value ? $this->entityManager->getReference($targetEntity, $value) : null);
                } elseif ($mapping["type"] & ClassMetadataInfo::TO_MANY && is_array($value)) {
                    $child = $entityMeta->getFieldValue($user, $field);
                    $child->clear();

                    foreach ($value as $val) {
                        $child->add($this->entityManager->getReference($targetEntity, $val));
                    }
                }
            } else {
                throw new InvalidParametersException(sprintf("Invalid user field: %s", $field));
            }
        }
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
