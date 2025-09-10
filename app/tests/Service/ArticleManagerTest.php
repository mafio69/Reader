<?php

namespace App\Tests\Service;

use App\Entity\ArticleSummary;
use App\Service\ArticleManager;
use App\Service\SummarizationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ArticleManagerTest extends TestCase
{
    private $summarizationService;
    private $entityManager;
    private $logger;
    private $articleManager;

    protected function setUp(): void
    {
        $this->summarizationService = $this->createMock(SummarizationService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->articleManager = new ArticleManager(
            $this->summarizationService,
            $this->entityManager,
            $this->logger
        );
    }

    public function testSummarizeAndSaveSuccess()
    {
        $url = 'http://example.com';
        $summaryText = 'This is a summary.';

        $articleSummary = new ArticleSummary();
        $articleSummary->setOriginalUrl($url);

        $this->summarizationService->expects($this->once())
            ->method('summarizeUrl')
            ->with($url)
            ->willReturn($summaryText);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($summaryText) {
                return $entity instanceof ArticleSummary &&
                       $entity->getSummary() === $summaryText &&
                       $entity->getCreatedAt() instanceof \DateTimeImmutable;
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->logger->expects($this->exactly(2))
            ->method('info');

        $this->articleManager->summarizeAndSave($articleSummary);

        $this->assertSame($summaryText, $articleSummary->getSummary());
        $this->assertInstanceOf(\DateTimeImmutable::class, $articleSummary->getCreatedAt());
    }

    public function testSummarizeAndSaveThrowsExceptionForEmptyUrl()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('URL nie może być pusty.');

        $articleSummary = new ArticleSummary();
        // URL is intentionally not set

        $this->articleManager->summarizeAndSave($articleSummary);
    }

    public function testSummarizeAndSaveThrowsExceptionWhenSummarizationFails()
    {
        $url = 'http://example.com';
        $articleSummary = new ArticleSummary();
        $articleSummary->setOriginalUrl($url);

        $this->summarizationService->expects($this->once())
            ->method('summarizeUrl')
            ->with($url)
            ->willReturn(null);

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Nie udało się wygenerować streszczenia.');

        $this->articleManager->summarizeAndSave($articleSummary);
    }

    public function testSummarizeAndSaveThrowsExceptionOnDatabaseError()
    {
        $url = 'http://example.com';
        $summaryText = 'This is a summary.';

        $articleSummary = new ArticleSummary();
        $articleSummary->setOriginalUrl($url);

        $this->summarizationService->expects($this->once())
            ->method('summarizeUrl')
            ->with($url)
            ->willReturn($summaryText);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->articleManager->summarizeAndSave($articleSummary);
    }
}
