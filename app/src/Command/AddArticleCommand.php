<?php

namespace App\Command;

use App\Entity\ArticleSummary;
use App\Service\ArticleManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-article',
    description: 'Summarizes and saves a new article from a given URL.',
)]
class AddArticleCommand extends Command
{
    private ArticleManager $articleManager;

    public function __construct(ArticleManager $articleManager)
    {
        parent::__construct();
        $this->articleManager = $articleManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'The URL of the article to summarize.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        $io->info(sprintf('Attempting to summarize article from: %s', $url));

        try {
            $summary = new ArticleSummary();
            $summary->setOriginalUrl($url);

            $this->articleManager->summarizeAndSave($summary);

            $io->success('Article summarized and saved successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('An error occurred: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
