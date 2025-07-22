<?php

namespace App\Data\Catalog\Flows;

class RewriteConfigurationData extends FlowConfiguration
{
    public function __construct(
        /**
         * The prompt for the rewrite
         */
        public string $prompt,

    ) {
        
     }

}
