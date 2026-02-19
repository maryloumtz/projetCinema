<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EncoreAvailabilityExtension extends AbstractExtension
{
    public function __construct(private ParameterBagInterface $params)
    {
    }

    /**
    * Expose a simple check to avoid exceptions when Encore build is missing.
    */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_is_built', [$this, 'isBuilt']),
        ];
    }

    public function isBuilt(): bool
    {
        $projectDir = (string) $this->params->get('kernel.project_dir');

        return file_exists($projectDir . '/public/build/entrypoints.json');
    }
}
