<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Command;

use Agit\BaseBundle\Command\SingletonCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserAddCommand extends ContainerAwareCommand
{
    use SingletonCommandTrait;

    protected function configure()
    {
        $this
            ->setName("agit:user:add")
            ->setDescription("Add a user to the users database")
            ->addArgument("email", InputArgument::REQUIRED, "e-mail address.")
            ->addArgument("name", InputArgument::REQUIRED, "full name (use quotes if you want to use spaces).")
            ->addArgument("role", InputArgument::OPTIONAL, "user role identifier.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->flock(__FILE__)) {
            return;
        }

        $user = $this->getContainer()->get("agit.user")->createUser(
            $input->getArgument("name"),
            $input->getArgument("email"),
            $input->getArgument("role")
        );

        $entityManager = $this->getContainer()->get("doctrine.orm.entity_manager");
        $entityManager->persist($user);
        $entityManager->flush();
    }
}
