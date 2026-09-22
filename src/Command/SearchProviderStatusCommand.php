<?php

declare(strict_types=1);

namespace App\Searching\Command;

use App\Searching\Contract\Provider\SearchProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines the search provider status command responsibility within the Searching component runtime and its typed boundaries.
 */
#[AsCommand(name: 'searching:provider:status')]
final class SearchProviderStatusCommand extends Command
{
    public function __construct(private readonly SearchProviderInterface $searchProvider)
    {
        parent::__construct();
    }

    /**
     * Executes the execute operation through the Searching component runtime boundary.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = $this->searchProvider->getStatus();
        $output->writeln($status->nameEntity.': '.$status->status);

        return Command::SUCCESS;
    }
}
