<?php
/**
 * File containing the ezcTemplateIdentifierSourceToTstParser class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Parser for identifier types.
 *
 * Identifiers consists of a-z, A-Z, underscore (_) and numbers only.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateIdentifierSourceToTstParser extends ezcTemplateTypeSourceToTstParser
{
    /**
     * The identifier which was found while parsing or null if no identifier
     * has been found yet.
     * @var string
     */
    public $identifierName;

    /**
     * Passes control to parent.
     */
    function __construct( ezcTemplateParser $parser, /*ezcTemplateSourceToTstParser*/ $parentParser, /*ezcTemplateCursor*/ $startCursor )
    {
        parent::__construct( $parser, $parentParser, $startCursor );
        $this->identifierName = null;
    }

    /**
     * Parses the identifier types by looking for allowed characters.
     */
    protected function parseCurrent( ezcTemplateCursor $cursor )
    {
        if ( !$cursor->atEnd() )
        {
            $matches = $cursor->pregMatch( "#^[a-zA-Z_][a-zA-Z0-9_]*#" );
            if ( $matches !== false )
            {
                $cursor->advance( strlen( $matches[0][0] ) );
                $identifier = $this->parser->createType( $this->startCursor, $cursor );
                $identifier->value = (string)$matches[0][0];
                $this->identifierName = $identifier->value;
                $this->element = $identifier;
                $this->appendElement( $identifier );
                return true;
            }
        }
        return false;
    }

    /**
     * Returns grammar description for <i>Identifier</i> rule.
     *
     * @see ezcTemplateSourceToTstParser::getGrammarDescription()
     * @return ezcTemplateGrammarDescription
     */
    static public function getGrammarDescription()
    {
        return new ezcTemplateGrammarDescription( array( "Identifier" => "( Letter | '_' ) ( Letter | Digit | '_' )*" ),
                                                  array( array( "class" => "ezcTemplateSourceToTstParser",
                                                                "rules" => array( "Letter", "Digit" ) ) ) );
    }
}

?>
