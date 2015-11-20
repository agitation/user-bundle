<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Agit\CommonBundle\Command\AbstractCommand;
use Agit\UserBundle\Entity\User;

class UserPasswdCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('agit:user:passwd')
            ->setDescription('Add a user to the users database')
            ->addArgument('email', InputArgument::REQUIRED, 'e-mail address.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->flock(__FILE__)) return;

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository("AgitUserBundle:User")->findOneBy(['email' => $input->getArgument('email')]);

        if (!$user)
            throw new \Exception("User not found.");

        $dialog = $this->getHelper('dialog');
        $password1 = $dialog->askHiddenResponse($output, sprintf('New password for %s: ', $user->getName()));
        $password2 = $dialog->askHiddenResponse($output, 'Confirm new password: ');

        $this->getContainer()->get('agit.validation')
            ->validate('password', $password1, $password2);

        $factory = $this->getContainer()->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $pwdHash = $encoder->encodePassword($password1, $user->getSalt());

        $user->setPassword($pwdHash);
        $user->eraseCredentials();

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
