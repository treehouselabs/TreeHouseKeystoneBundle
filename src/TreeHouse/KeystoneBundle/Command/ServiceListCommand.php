<?php

namespace TreeHouse\KeystoneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TreeHouse\KeystoneBundle\Manager\ServiceManager;
use TreeHouse\KeystoneBundle\Model\UserInterface;

class ServiceListCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('keystone:service:list')
            ->setDescription('Lists available services.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command lists all available services.

  <info>php app/console %command.name%</info>

EOT
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $divider = str_pad('', 50, '=');

        $services = $this->getServiceManager()->getServices();
        foreach ($services as $service) {
            $output->writeln(
                sprintf(
                    'Service name: <info>%s</info>, type: <info>%s</info>',
                    $service->getName(),
                    $service->getType()
                )
            );

            $output->writeln($divider);

            foreach ($service->getEndpoints() as $key => $endpoint) {
                $output->writeln(sprintf('Endpoint <comment>%d</comment>:', $key));
                $output->writeln(sprintf('Public url: <comment>%s</comment>', $endpoint->getPublicUrl()));
                $output->writeln(sprintf('Admin url:  <comment>%s</comment>', $endpoint->getAdminUrl()));
                $output->writeln('');
            }
            $output->writeln('');
        }
    }

    /**
     * @return UserProviderInterface
     */
    protected function getUserProvider()
    {
        $userProviderServiceId = $this->getContainer()->getParameter('tree_house.keystone.user_provider.id');

        return $this->getContainer()->get($userProviderServiceId);
    }

    /**
     * @param string $username
     *
     * @return UserInterface
     */
    protected function loadUserByUsername($username)
    {
        return $this->getUserProvider()->loadUserByUsername($username);
    }

    /**
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        return $this->getContainer()->get('tree_house.keystone.service_manager');
    }
}
