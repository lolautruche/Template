<?php
/**
 * File containing the ezcTemplateArraySourceToTstParser class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Parser for array types.
 *
 * Arrays are defined in the same way as in PHP.
 * <code>
 * array( [<expression> => ] <expression> [, [<expression> => ] <expression> ] )
 * </code>
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateArraySourceToTstParser extends ezcTemplateLiteralSourceToTstParser
{

    /**
     * Passes control to parent.
     */
    function __construct( ezcTemplateParser $parser, /*ezcTemplateSourceToTstParser*/ $parentParser, /*ezcTemplateCursor*/ $startCursor )
    {
        parent::__construct( $parser, $parentParser, $startCursor );
    }

    /**
     * Parses the array types by looking for 'array(...)' and then using the
     * generic expression parser (ezcTemplateExpressionSourceToTstParser) to fetch the
     * keys and values.
     * @todo Keys and values should be allow to be expressions, switch sub-parser
     */
    protected function parseCurrent( ezcTemplateCursor $cursor )
    {
        // skip whitespace and comments
        if ( !$this->findNextElement() )
            return false;

        // @todo Check for non-lowercase array entry, give partial success then.
        $name = $cursor->pregMatch( "#^array[^\w]#i", false );
        if ( $name === false )
        {
            return false;
        }

        $lower = strtolower( $name );
        if ( $name !== $lower )
        {
            $this->findNonLowercase();
            throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_ARRAY_NOT_LOWERCASE); 
        }

        $cursor->advance( 5 );

        // skip whitespace and comments
        $this->findNextElement();

        if ( !$cursor->match('(') )
        {
            throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_ROUND_BRACKET_OPEN ); 
        }

        $currentArray = array();
        $expectItem = true;
        while ( true )
        {
            // skip whitespace and comments
            if ( !$this->findNextElement() )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_ROUND_BRACKET_CLOSE ); 
            }

            if ( $cursor->current() == ')' )
            {
                $cursor->advance();
                $array = $this->parser->createLiteral( $this->startCursor, $cursor );
                $array->value = $currentArray;
                $this->value = $array->value;
                $this->element = $array;
                $this->appendElement( $array );
                return true;
            }

            if( !$expectItem )
            {
                throw new ezcTemplateSourceToTstParserException ( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_ROUND_BRACKET_CLOSE_OR_COMMA);
            }

            // Check for type
            if ( !$expectItem || !$this->parseRequiredType( 'Literal' ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_LITERAL ); 
            }

            $this->findNextElement();

            if ( $cursor->match( '=>' ) )
            {
                // Found the array key. Store it, and continue with the search for the value.
                $arrayKey = $this->lastParser->value;
                $this->findNextElement();

                // We have the key => value syntax so we need to find the value
                if ( !$this->parseRequiredType( 'Literal' ) )
                {
                    throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_LITERAL ); 
                }

                // Store the value.
                $currentArray[$arrayKey] = $this->lastParser->value;
            }
            else
            {
                // Store the value.
                $currentArray[] = $this->lastParser->value;
            }

            $this->findNextElement();

            // We allow a comma after the key/value even if there are no more
            // entries. This is compatible with PHP syntax.
            if ( $cursor->match(',') )
            {
                $this->findNextElement();
                $expectItem = true;
            }
            else
            {
                $expectItem = false;
            }
        }
    }

    public function getTypeName()
    {
        return "array";
    }
}

?>
