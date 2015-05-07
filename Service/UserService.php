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
use Agit\ValidationBundle\Service\ValidationService;
use Agit\IntlBundle\Service\Translate;
use Agit\UserBundle\Exception\UnauthorizedException;
use Agit\UserBundle\Entity\User;

class UserService
{
    private $SecurityContext;

    private $SecurityEncoderFactory;

    private $EntityManager;

    private $ValidationService;

    private $User = false;

    public function __construct(SecurityContext $SecurityContext, EncoderFactory $SecurityEncoderFactory, EntityManager $EntityManager, ValidationService $ValidationService)
    {
        $this->SecurityContext = $SecurityContext;
        $this->SecurityEncoderFactory = $SecurityEncoderFactory;
        $this->EntityManager = $EntityManager;
        $this->ValidationService = $ValidationService;
    }

    public function authenticate($username, $password)
    {
        if (!$this->ValidationService->isValid('email', $username))
            throw new UnauthorizedException(Translate::getInstance()->t("Authentication has failed. Please check your user name and your password."));

        $User = $this->EntityManager->getRepository('AgitUserBundle:User')
            ->findOneBy(['email' => $username, 'active' => true]);

        if (!$User)
            throw new UnauthorizedException(Translate::getInstance()->t("Authentication has failed. Please check your user name and your password."));

        $password = $this->SecurityEncoderFactory->getEncoder($User)
            ->encodePassword($password, $User->getSalt());

        if ($password !== $User->getPassword())
            throw new UnauthorizedException(Translate::getInstance()->t("Authentication has failed. Please check your user name and your password."));

        $this->setCurrentUser($User);
    }

    public function getCurrentUser()
    {
        $User = null;

        if ($this->User === false)
        {
            $SecurityToken = $this->SecurityContext->getToken();

            if (is_object($SecurityToken) && is_callable([$SecurityToken, 'getUser']))
                $User = $SecurityToken->getUser();

            $this->User = ($User instanceof User) ? $User : null;
        }

        return $this->User;
    }

    public function currentUserCan($cap)
    {
        $can = false;
        $User = $this->getCurrentUser();

        if ($User)
            $can = $User->hasCapability($cap);

        return $can;
    }
}