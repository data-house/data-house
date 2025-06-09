<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Catalog Language Lines
    |--------------------------------------------------------------------------
    */

    "field_types" => [
        'label' => [
            'TEXT' => 'Short text',
            'MULTILINE_TEXT' => 'Long text',
            'NUMBER' => 'Number',
            'DATETIME' => 'Date and time',
            'BOOLEAN' => 'Yes/No',
            'SKOS_CONCEPT' => 'Picklist',
        ],
        'description' => [
            'TEXT' => 'Use for single-line text entries like titles, names or short descriptions (max 250 characters)',
            'MULTILINE_TEXT' => 'Use for detailed descriptions, notes or any content requiring multiple paragraphs (up to 6000 characters)',
            'NUMBER' => 'Use for any numeric values like quantities, measurements or scores',
            'DATETIME' => 'Use for capturing specific dates and times like creation dates or event scheduling',
            'BOOLEAN' => 'Use for binary choices or toggles like active/inactive, yes/no, or true/false states',
            'SKOS_CONCEPT' => 'Use for selecting predefined values from a controlled vocabulary or taxonomy',
        ],
    ],

];
