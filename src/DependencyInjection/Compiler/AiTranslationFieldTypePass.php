<?php

declare(strict_types=1);

namespace Akawaka\SuluMultiCKEditorBundle\DependencyInjection\Compiler;

use Akawaka\SuluMultiCKEditorBundle\Metadata\AiRemappingMetadataProviderRegistry;
use Akawaka\SuluMultiCKEditorBundle\Metadata\AiTranslationFormMetadataLoader;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataLoaderInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replaces the FormMetadataLoader and MetadataProviderRegistry used by the AI
 * translation subscriber with decorators that remap custom field types
 * (e.g. configurable_text_editor → text_editor), so the subscriber treats
 * them as translatable — without affecting the rest of the admin UI.
 *
 * Two code paths need to be intercepted:
 *  1. FormMetadataLoaderInterface → handles regular page/article/snippet template fields
 *  2. MetadataProviderRegistry    → handles global blocks (loaded separately)
 */
final class AiTranslationFieldTypePass implements CompilerPassInterface
{
    private const SERVICE_ID = 'sulu_ai_platform.full_page_translation_subscriber';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::SERVICE_ID)) {
            return;
        }

        $subscriberDef = $container->getDefinition(self::SERVICE_ID);

        foreach ($subscriberDef->getArguments() as $index => $argument) {
            if (!$argument instanceof Reference) {
                continue;
            }

            $serviceId = (string) $argument;
            if (!$container->has($serviceId)) {
                continue;
            }

            $class = $container->findDefinition($serviceId)->getClass();
            if (null === $class) {
                continue;
            }

            if (\is_a($class, FormMetadataLoaderInterface::class, true)) {
                $decoratorId = 'akawaka_sulu_multi_text_editor.ai_translation_form_metadata_loader';
                $container->setDefinition($decoratorId, new Definition(
                    AiTranslationFormMetadataLoader::class,
                    [new Reference($serviceId)],
                ));
                $subscriberDef->replaceArgument($index, new Reference($decoratorId));

                continue;
            }

            if (\is_a($class, MetadataProviderRegistry::class, true)) {
                $registryId = 'akawaka_sulu_multi_text_editor.ai_remapping_metadata_provider_registry';
                $container->setDefinition($registryId, new Definition(
                    AiRemappingMetadataProviderRegistry::class,
                    [new Reference($serviceId)],
                ));
                $subscriberDef->replaceArgument($index, new Reference($registryId));
            }
        }
    }
}
