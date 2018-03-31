<?php
declare(strict_types=1);

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserSetPropertyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('agit:user:set')
            ->setDescription('Updates a property of a primary user entity. NOTE: The user entity is not being validated, because we want to allow modifying incomplete user entities.')
            ->addArgument('user', InputArgument::REQUIRED, 'user e-mail or ID')
            ->addArgument('name', InputArgument::REQUIRED, 'field name')
            ->addArgument('value', InputArgument::REQUIRED, 'value or reference');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $input->getArgument('user');
        $field = $input->getArgument('name');
        $value = $input->getArgument('value');

        if (is_numeric($userId))
        {
            $userId = (int) $userId;
        }

        $userService = $this->getContainer()->get('agit.user');
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $user = $userService->getUser($userId);
        $userService->setUserField($user, $field, $value);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
