<?php

namespace App\Tests\Command;

use App\Command\AddArticleCommand;
use App\Service\ArticleManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AddArticleCommandTest extends TestCase
{
    private $articleManager;

    protected function setUp(): void
    {
        $this->articleManager = $this->createMock(ArticleManager::class);
    }

    public function testExecuteSuccess()
    {
        $application = new Application();
        $application->add(new AddArticleCommand($this->articleManager));

        $command = $application->find('app:add-article');
        $commandTester = new CommandTester($command);

        $this->articleManager->expects($this->once())
            ->method('summarizeAndSave');

        $commandTester->execute([
            'url' => 'http://example.com',
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Article summarized and saved successfully!', $output);
    }

    public function testExecuteFailure()
    {
        $application = new Application();
        $application->add(new AddArticleCommand($this->articleManager));

        $command = $application->find('app:add-article');
        $commandTester = new CommandTester($command);

        $this->articleManager->expects($this->once())
            ->method('summarizeAndSave')
            ->willThrowException(new \Exception('Test error'));

        $commandTester->execute([
            'url' => 'http://example.com',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('An error occurred: Test error', $output);
        $this->assertSame(1, $commandTester->getStatusCode());
    }
}
