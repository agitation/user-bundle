<?php
/**
 * @package    agitation/user
 * @link       http://github.com/agitation/AgitUserBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Agit\CommonBundle\Command\SingletonCommandTrait;
use Agit\UserBundle\Entity\User;

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
            ->addArgument("role", InputArgument::REQUIRED, "user role identifier.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->flock(__FILE__)) return;

        $entityManager = $this->getContainer()->get("doctrine.orm.entity_manager");

        // find out which class implements UserConfig
        $userConfigMetadata = $entityManager->getClassMetadata("Agit\UserBundle\Entity\UserConfigInterface");
        $userConfigClass = $userConfigMetadata->name;

        // some garbage to fill the password/salt fields. Real values are set through the agit:user:password command.
        $authGarbage = sha1(microtime(true));

        $user = new User();
        $user->setName($input->getArgument("name"));
        $user->setEmail($input->getArgument("email"));
        $user->setRole($entityManager->getReference("AgitUserBundle:UserRole", $input->getArgument('role')));
        $user->setActive(true);
        $user->setSalt($authGarbage);
        $user->setPassword($authGarbage);
        $user->setConfig($userConfigClass::getDefaultConfig());
        $user->getConfig()->setUser($user);

        $errors = $this->getContainer()->get("validator")->validate($user);

        if (count($errors) > 0)
            throw new \Exception((string)$errors);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
