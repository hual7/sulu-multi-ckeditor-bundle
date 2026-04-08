<?php

declare(strict_types=1);

namespace Akawaka\SuluMultiCKEditorBundle\Metadata;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\MetadataInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderInterface;

/**
 * Wraps a MetadataProvider and remaps custom field types to their base types
 * so the AI translation subscriber treats them as translatable.
 */
final class AiRemappingFormMetadataProvider implements MetadataProviderInterface
{
    public function __construct(
        private readonly MetadataProviderInterface $inner,
    ) {
    }

    public function getMetadata(string $key, string $locale, array $metadataOptions): MetadataInterface
    {
        $metadata = $this->inner->getMetadata($key, $locale, $metadataOptions);

        if ($metadata instanceof TypedFormMetadata) {
            foreach ($metadata->getForms() as $form) {
                AiTranslationFormMetadataLoader::remapFieldTypes($form);
            }
        } elseif ($metadata instanceof FormMetadata) {
            AiTranslationFormMetadataLoader::remapFieldTypes($metadata);
        }

        return $metadata;
    }
}
