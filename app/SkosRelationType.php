<?php

namespace App;

/**
 * SKOS relation types.
 * 
 * We don't support broaderTransitive and narrowerTransitive
 * 
 * @see https://www.w3.org/TR/2009/REC-skos-reference-20090818/#semantic-relations
 * @see https://www.w3.org/TR/2009/REC-skos-reference-20090818/#mapping
 */
enum SkosRelationType: int
{

    // semantic relations

    case RELATED = 100;
    
    case RELATED_MATCH = 110;

    case BROADER_TRANSITIVE = 200; // TODO: handle them during import
    
    case BROADER = 210;
    
    case BROAD_MATCH = 211;
    
    case NARROWER_TRANSITIVE = 300; // TODO: handle them during import

    case NARROWER = 310;
    
    case NARROW_MATCH = 311;


    // mapping relations

    case CLOSE_MATCH = 400;
    
    case EXACT_MATCH = 410;
    

    public static function mappings(): array
    {
        return [
            self::RELATED_MATCH,
            self::BROAD_MATCH,
            self::NARROW_MATCH,
            self::CLOSE_MATCH,
            self::EXACT_MATCH,
        ];
    }

    /*
     * skos:semanticRelation
     *  |
     *  +- skos:related
     *  |   |
     *  |   +- skos:relatedMatch
     *  |
     *  +- skos:broaderTransitive
     *  |   |
     *  |   +- skos:broader
     *  |       |
     *  |       +- skos:broadMatch
     *  |
     *  +- skos:narrowerTransitive
     *  |   |
     *  |   +- skos:narrower
     *  |       |
     *  |       +- skos:narrowMatch
     *  |
     *  +- skos:mappingRelation
     *      |
     *      +- skos:closeMatch
     *      |   |
     *      |   +- skos:exactMatch
     *      |
     *      +- skos:relatedMatch
     *      |
     *      +- skos:broadMatch
     *      |
     *      +- skos:narrowMatch 
     */
}
