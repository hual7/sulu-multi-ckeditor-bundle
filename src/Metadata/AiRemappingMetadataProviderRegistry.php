<?php

declare(strict_types=1);

namespace Akawaka\SuluMultiCKEditorBundle\Metadata;

use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Wraps MetadataProviderRegistry to remap custom field types in form metadata
 * loaded via the 'form' provider (used for global blocks in the AI translation subscriber).
 */
final readonly class AiRemappingMetadataProviderRegistry extends MetadataProviderRegistry
{
    public function __construct(
        private MetadataProviderRegistry $inner,
    ) {
        // Pass an empty ServiceLocator to satisfy the parent constructor.
        // getMetadataProvider() is overridden and always delegates to $inner.
        parent::__construct(new ServiceLocator([]));
    }

    public function getMetadataProvider(string $type): MetadataProviderInterface
    {
        $provider = $this->inner->getMetadataProvider($type);

        if ('form' === $type) {
            return new AiRemappingFormMetadataProvider($provider);
        }

        return $provider;
    }
}
