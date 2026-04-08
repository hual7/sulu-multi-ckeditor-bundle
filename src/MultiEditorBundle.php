<?php

declare(strict_types=1);

namespace Akawaka\SuluMultiCKEditorBundle;

use Akawaka\SuluMultiCKEditorBundle\DependencyInjection\Compiler\AiTranslationFieldTypePass;
use Akawaka\SuluMultiCKEditorBundle\DependencyInjection\MultiEditorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MultiEditorBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MultiEditorExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AiTranslationFieldTypePass());
    }
}