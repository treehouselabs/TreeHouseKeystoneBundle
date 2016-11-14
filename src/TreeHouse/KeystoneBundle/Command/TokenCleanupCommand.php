<?php

namespace TreeHouse\KeystoneBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TreeHouse\KeystoneBundle\Entity\Token;

class TokenCleanupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('keystone:token:cleanup');
        $this->setDescription('Cleanup expired tokens.');
        $this->addArgument(
            'expired-since',
            InputArgument::OPTIONAL,
            'Sets the time the token has to be expired for. Can be any valid DateTime constructor string.',
            '3 hours ago'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $purgeDate = new \DateTime($input->getArgument('expired-since'));

        /** @var EntityRepository $tokenRepository */
        $tokenRepository = $this->getContainer()->get('doctrine')->getRepository('TreeHouseKeystoneBundle:Token');

        /** @var ObjectManager $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager();

        $queryBuilder = $tokenRepository->createQueryBuilder('t');
        $queryBuilder->where('t.expiresAt < :date');
        $queryBuilder->setParameter('date', $purgeDate->format('Y-m-d H:i:s'));
        $queryBuilder->setMaxResults(1000);

        $counter = 0;

        /** @var Token[] $tokens */
        while ($tokens = $queryBuilder->getQuery()->getResult()) {
            foreach ($tokens as $token) {
                $output->writeln(sprintf('Removing token %s', $token->getId()), OutputInterface::VERBOSITY_VERBOSE);
                $entityManager->remove($token);
                $entityManager->flush();
            }

            $counter += count($tokens);
        }

        $output->writeln(sprintf('All done. Removed %d tokens.', $counter));
    }
}
