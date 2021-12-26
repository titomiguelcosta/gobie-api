<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Schema\Visitor\Graphviz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DoctrineSchemaGraphvizCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'doctrine:schema:graphviz';

    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Get dot from database schema')
            ->setHelp(sprintf('Usage: bin/console %s > /tmp/db-schema.dot && dot -Tpdf /tmp/db-schema.dot > docs/db-schema.pdf && rm -f /tmp/db-schema.dot', $this->getName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln($this->getDot());

        return 0;
    }

    protected function getDot(): string
    {
        $schema = $this->em->getConnection()->getSchemaManager()->createSchema();
        $visitor = new Graphviz();
        $schema->visit($visitor);

        return $visitor->getOutput();
    }
}
