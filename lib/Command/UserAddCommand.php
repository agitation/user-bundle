<?php
declare(strict_types=1);
/*
 * @package    agitation/user-bundle
 * @link       http://github.com/agitation/user-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\UserBundle\Command;

use Agit\UserBundle\Exception\InvalidParametersException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserAddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('agit:user:add')
            ->setDescription('Add a user to the users database')
            ->addArgument('fields', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'fields, as "key=value"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fields = [];

        foreach ($input->getArgument('fields') as $value)
        {
            $parts = explode('=', $value, 2);

            if (count($parts) !== 2)
            {
                throw new InvalidParametersException(sprintf('Invalid field value: %s.', $value));
            }

            $fields[$parts[0]] = $parts[1];
        }

        $user = $this->getContainer()->get('agit.user')->createUser($fields);

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($user);
        $entityManager->flush();
    }
}
