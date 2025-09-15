<?php

namespace App\Service;

use App\Entity\ArticleSummary;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ArticleManager
{
    private SummarizationService $summarizationService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private DatabaseConnectionManager $connectionManager;

    public function __construct(
        SummarizationService $summarizationService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        DatabaseConnectionManager $connectionManager,
    ) {
        $this->summarizationService = $summarizationService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->connectionManager = $connectionManager;
    }

    public function summarizeAndSave(ArticleSummary $articleSummary): void
    {
        $originalUrl = $articleSummary->getOriginalUrl();
        if (!$originalUrl) {
            throw new \InvalidArgumentException('URL nie może być pusty.');
        }

        $this->logger->info(sprintf('ArticleManager: Próba streszczenia URL: %s', $originalUrl));
        $summaryText = $this->summarizationService->summarizeUrl($originalUrl);

        if (null === $summaryText) {
            $this->logger->error(sprintf('ArticleManager: Nie udało się uzyskać streszczenia dla URL: %s', $originalUrl));
            throw new \RuntimeException('Nie udało się wygenerować streszczenia.');
        }

        $articleSummary->setSummary($summaryText);
        $articleSummary->setCreatedAt(new \DateTimeImmutable());

        // Use DatabaseConnectionManager for robust database operations with retry logic
        $this->connectionManager->executeWithRetry(function () use ($articleSummary, $originalUrl) {
            $this->entityManager->beginTransaction();

            try {
                $this->entityManager->persist($articleSummary);
                $this->entityManager->flush();
                $this->entityManager->commit();

                $this->logger->info(sprintf('ArticleManager: Artykuł z URL %s został pomyślnie streszczony i zapisany.', $originalUrl));
            } catch (\Exception $e) {
                if ($this->entityManager->getConnection()->isTransactionActive()) {
                    $this->entityManager->rollback();
                }
                throw $e;
            }
        });
    }
}
