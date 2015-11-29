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
use Agit\CommonBundle\Command\SingletonCommandTrait;
use Agit\UserBundle\Entity\User;

class UserAddCommand
{
    use SingletonCommandTrait;

    protected function configure()
    {
        $this
            ->setName('agit:user:add')
            ->setDescription('Add a user to the users database')
            ->addArgument('email', InputArgument::REQUIRED, 'e-mail address.')
            ->addArgument('name', InputArgument::REQUIRED, 'full name (use quotes if you want to use spaces).')
            ->addArgument('role', InputArgument::OPTIONAL, 'user role identifier (optional).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->flock(__FILE__)) return;

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $role = $input->getArgument('role')
            ? $entityManager->getReference("AgitUserBundle:UserRole", $input->getArgument('role'))
            : null;

        $user = new User();
        $user->setName($input->getArgument('name'));
        $user->setEmail($input->getArgument('email'));
        $user->setRole($role);
        $user->setActive(true);
        $user->setPassword(sha1(microtime(true))); // just some garbage

        $errors = $this->getContainer()->get('validator')->validate($user);

        if (count($errors) > 0)
            throw new \Exception((string)$errors);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
