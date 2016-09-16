<?php

/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Command;

use Exception;
use Agit\BaseBundle\Command\SingletonCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserPasswdCommand extends ContainerAwareCommand
{
    use SingletonCommandTrait;

    protected function configure()
    {
        $this
            ->setName("agit:user:passwd")
            ->setDescription("Add a user to the users database")
            ->addArgument("email", InputArgument::REQUIRED, "e-mail address.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->flock(__FILE__)) {
            return;
        }

        $entityManager = $this->getContainer()->get("doctrine.orm.entity_manager");

        $user = $entityManager->getRepository("AgitUserBundle:UserInterface")
            ->findOneBy(["email" => $input->getArgument("email")]);

        if (! $user) {
            throw new Exception("User not found.");
        }

        $dialog = $this->getHelper("dialog");
        $password1 = $dialog->askHiddenResponse($output, sprintf("New password for %s: ", $user->getName()));
        $password2 = $dialog->askHiddenResponse($output, "Confirm new password: ");

        $this->getContainer()->get("agit.validation")->validate("password", $password1, $password2);

        $this->getContainer()->get("agit.user")->setPassword($user, $password1);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
