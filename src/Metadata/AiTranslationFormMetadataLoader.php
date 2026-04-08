<?php

declare(strict_types=1);

namespace Akawaka\SuluMultiCKEditorBundle\Metadata;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataLoaderInterface;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\SectionMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\MetadataInterface;

/**
 * Decorates the FormMetadataLoader for the AI translation subscriber only.
 * Remaps custom field types to their base types so the AI translation
 * subscriber recognises them as translatable.
 *
 * @see \Sulu\Bundle\AiPlatformBundle\Features\FullContentTranslation\AbstractFullContentTranslationSubscriber
 */
final class AiTranslationFormMetadataLoader implements FormMetadataLoaderInterface
{
    /**
     * Maps custom field types → their base type for AI translation purposes.
     *
     * @var array<string, string>
     */
    public const FIELD_TYPE_MAP = [
        'configurable_text_editor' => 'text_editor',
    ];

    public function __construct(
        private readonly FormMetadataLoaderInterface $inner,
    ) {
    }

    public function getMetadata(string $key, string $locale, array $metadataOptions): ?MetadataInterface
    {
        $metadata = $this->inner->getMetadata($key, $locale, $metadataOptions);

        if ($metadata instanceof TypedFormMetadata) {
            foreach ($metadata->getForms() as $form) {
                self::remapFieldTypes($form);
            }
        } elseif ($metadata instanceof FormMetadata) {
            self::remapFieldTypes($metadata);
        }

        return $metadata;
    }

    public static function remapFieldTypes(FormMetadata $form): void
    {
        foreach ($form->getItems() as $item) {
            if ($item instanceof SectionMetadata) {
                foreach ($item->getItems() as $field) {
                    self::remapField($field);
                }
            } elseif ($item instanceof FieldMetadata) {
                self::remapField($item);
            }
        }
    }

    public static function remapField(FieldMetadata $field): void
    {
        $mappedType = self::FIELD_TYPE_MAP[$field->getType()] ?? null;
        if (null !== $mappedType) {
            $field->setType($mappedType);
        }

        foreach ($field->getTypes() as $blockType) {
            self::remapFieldTypes($blockType);
        }
    }
}
