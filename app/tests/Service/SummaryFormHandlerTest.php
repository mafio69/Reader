<?php

namespace App\Tests\Service;

use App\Entity\ArticleSummary;
use App\Form\SummaryFormType;
use App\Service\ArticleManager;
use App\Service\SummaryFormHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SummaryFormHandlerTest extends TestCase
{
    private $formFactory;
    private $articleManager;
    private $logger;
    private $session;
    private $urlGenerator;
    private $formHandler;

    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->articleManager = $this->createMock(ArticleManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->formHandler = new SummaryFormHandler(
            $this->formFactory,
            $this->articleManager,
            $this->logger,
            $this->session,
            $this->urlGenerator
        );
    }

    public function testHandleSuccess()
    {
        $request = new Request([], ['summary_form' => ['originalUrl' => 'http://example.com']]);
        $form = $this->createMock(FormInterface::class);
        $flashBag = $this->createMock(FlashBagInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(SummaryFormType::class)
            ->willReturn($form);

        $form->expects($this->once())
            ->method('handleRequest')
            ->with($request);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->articleManager->expects($this->once())
            ->method('summarizeAndSave')
            ->with($this->isInstanceOf(ArticleSummary::class));

        $this->session->expects($this->once())
            ->method('get')
            ->with('flash_bag')
            ->willReturn($flashBag);

        $flashBag->expects($this->once())
            ->method('add')
            ->with('success', 'Artykuł został pomyślnie streszczony i zapisany!');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_summary_index')
            ->willReturn('/summary');

        $result = $this->formHandler->handle($request);

        $this->assertTrue($result['success']);
        $this->assertSame('/summary', $result['redirectToRoute']);
    }

    public function testHandleSummarizationFailure()
    {
        $request = new Request([], ['summary_form' => ['originalUrl' => 'http://example.com']]);
        $form = $this->createMock(FormInterface::class);
        $flashBag = $this->createMock(FlashBagInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $form->method('handleRequest')->with($request);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);

        $this->articleManager->expects($this->once())
            ->method('summarizeAndSave')
            ->with($this->isInstanceOf(ArticleSummary::class))
            ->willThrowException(new \RuntimeException('Summarization failed'));

        $this->logger->expects($this->once())
            ->method('error');

        $this->session->expects($this->once())
            ->method('get')
            ->with('flash_bag')
            ->willReturn($flashBag);

        $flashBag->expects($this->once())
            ->method('add')
            ->with('error', 'Wystąpił błąd podczas streszczania artykułu. Sprawdź logi aplikacji.');

        $result = $this->formHandler->handle($request);

        $this->assertFalse($result['success']);
        $this->assertSame($form, $result['form']);
    }

    public function testHandleFormNotSubmitted()
    {
        $request = new Request();
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);

        $form->method('handleRequest')->with($request);
        $form->method('isSubmitted')->willReturn(false);

        $result = $this->formHandler->handle($request);

        $this->assertFalse($result['success']);
        $this->assertSame($form, $result['form']);
    }
}
