<?php

namespace App\Data\Catalog\Flows;

class StructuredExtractionConfigurationData extends FlowConfiguration
{
    public function __construct(
        /**
         * The structured extraction response schema as a JSON Schema
         */
        public array $schema,

        /**
         * Field under each entry in the schema should be stored.
         * Each field can be mapped to a specific catalog field
         */
        public array $attributes_to_field,

        /**
         * Additional instructions to pass to the structured extraction executor
         */
        public ?string $instructions = null,

        /**
         * List of specific sections of documents that is the focus for the extraction
         */
        public ?array $document_sections = null,
    ) {
        // $this->variant = 'extract';
     }

}
