<?php

namespace App\EventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class EnvironmentBannerListener
{
    private Environment $twig;
    private string $environment;

    public function __construct(Environment $twig, string $environment)
    {
        $this->twig = $twig;
        $this->environment = $environment;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Dodaj zmienną do globalnego contextu Twig
        $this->twig->addGlobal('app_environment', $this->environment);
    }
}
