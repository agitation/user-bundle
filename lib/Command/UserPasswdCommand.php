<?php
declare(strict_types=1);
/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Command;

use Agit\UserBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserPasswdCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('agit:user:passwd')
            ->setDescription('Updates a user’s password')
            ->addArgument('user', InputArgument::REQUIRED, 'user e-mail or ID.')
            ->addArgument('class', InputArgument::OPTIONAL, sprintf('user entity class, default: %s', UserService::DEFAULT_USER_ENTITY_CLASS));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $userService = $this->getContainer()->get('agit.user');
        $entityClass = $input->getArgument('class') ?: UserService::DEFAULT_USER_ENTITY_CLASS;

        $userId = $input->getArgument('user');

        if (is_numeric($userId))
        {
            $userId = (int) $userId;
        }

        $user = $userService->getUser($userId, $entityClass);
        $dialog = $this->getHelper('dialog');
        $password1 = $dialog->askHiddenResponse($output, sprintf('New password for %s: ', $user->getName()));
        $password2 = $dialog->askHiddenResponse($output, 'Confirm new password: ');

        $this->getContainer()->get('agit.validation')->validate('password', $password1, $password2);

        $userService->setPassword($user, $password1);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
