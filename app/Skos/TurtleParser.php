<?php

namespace App\Skos;

use EasyRdf\Parser\Turtle;

class TurtleParser extends Turtle
{

    /**
     * Returns the namespaces included in the turtle file. 
     * 
     * Namespaces are keyprefixes as an array of prefix => URI
     * 
     * @return array $namespaces
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Get the base uri as configured at parsing time
     */
    public function getBaseUri(): ?string
    {
        return $this->baseUri?->toString();
    }


}