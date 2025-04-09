<?php

namespace App\Skos;

use EasyRdf\Graph;
use EasyRdf\Parser\Turtle;
use EasyRdf\RdfNamespace;

class SkosParser
{


    protected array $namespaces = [];
    
    protected ?string $baseUri = null;


    public function parse(string $filePath, ?string $baseUri = null): Graph
    {
        // Set common namespaces and prefix
        RdfNamespace::set('skos', 'http://www.w3.org/2004/02/skos/core#');
        RdfNamespace::set('skosmos', 'http://purl.org/net/skosmos#');
        RdfNamespace::set('skosext', 'http://purl.org/finnonto/schema/skosext#');
        RdfNamespace::delete('geo');
        RdfNamespace::set('wgs84', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
        RdfNamespace::set('isothes', 'http://purl.org/iso25964/skos-thes#');
        RdfNamespace::set('mads', 'http://www.loc.gov/mads/rdf/v1#');
        RdfNamespace::set('wd', 'http://www.wikidata.org/entity/');
        RdfNamespace::set('wdt', 'http://www.wikidata.org/prop/direct/');

        $graph = new Graph();
        $parser = new TurtleParser();
        $triplesCount = $parser->parse($graph, file_get_contents($filePath), 'turtle', $baseUri);

        $this->namespaces = $parser->getNamespaces();

        foreach ($this->namespaces as $prefix => $uri) {
            RdfNamespace::set($prefix, $uri);
        }

        $this->baseUri = $baseUri ?? $this->namespaces[""] ?? null;

        return $graph;
    }


    public function getBaseUri(): ?string
    {
        return $this->baseUri;
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

}