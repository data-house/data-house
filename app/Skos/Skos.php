<?php

namespace App\Skos;

use EasyRdf\Graph;
use EasyRdf\Parser\Turtle;

class Skos extends Graph
{

    public function __construct(
        protected array $namespaces = [],
    )
    {
        
    }



    /**
     * Returns the namespace prefixes as an array of prefix => URI
     * @return array $namespaces
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }



    // public function parse(string $filePath, string $baseUri): Graph
    // {
    //     $graph = new Graph();
    //     $parser = new Turtle();
    //     $triplesCount = $parser->parse($graph, file_get_contents($filePath), 'turtle', $baseUri);

    //     // dd($triplesCount);

    //     return $graph;
    // }

}