<?php
/**
 * File containing the ezcTemplateForeachLoopSourceToTstParser class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */
/**
 * Parser for {foreach} loop.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateForeachLoopSourceToTstParser extends ezcTemplateSourceToTstParser
{
    /**
     * Passes control to parent.
     */
    function __construct( ezcTemplateParser $parser, /*ezcTemplateSourceToTstParser*/ $parentParser, /*ezcTemplateCursor*/ $startCursor )
    {
        parent::__construct( $parser, $parentParser, $startCursor );
        $this->block = null;
    }

    /**
     * Parses the expression by using the ezcTemplateExpressionSourceToTstParser class.
     */
    protected function parseCurrent( ezcTemplateCursor $cursor )
    {
        $this->status = self::PARSE_PARTIAL_SUCCESS;

        // handle closing block
        if ( $this->block->isClosingBlock )
        {
            if ( $this->parser->debug )
                echo "Starting end of foreach loop\n";

            $this->findNextElement();
            if ( !$this->parentParser->atEnd( $cursor, null, false ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            $cursor->advance();

            $el = $this->parser->createForeachLoop( $this->startCursor, $cursor );
            $el->isClosingBlock = true;
            $this->appendElement( $el );
            return true;
        }

        // handle opening block

        if ( $this->parser->debug )
            echo "Starting foreach loop\n";

        // parse required part: "<array> as <varName>"

        $el = $this->parser->createForeachLoop( $this->startCursor, $cursor );

        $this->findNextElement();
        if ( !$this->parseOptionalType( 'Expression', null, false ) )
        {
            throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
        }
        $el->array = $this->lastParser->rootOperator;

        $this->findNextElement();
        if( !$this->currentCursor->match('as') )
        {
            throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_AS );
        }
        $this->findNextElement();
        if ( !$this->parseRequiredType( 'Variable', null, false ) )
        {
            throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
        }
        $el->itemVariableName = $this->lastParser->element->name;

        $this->findNextElement();

        $canBeArrow = true;

        // parse "=> $itemVar" clause if we're not at the end yet
        if ( $cursor->match ('=>' ) )
        {
            $canBeArrow = false;
            $this->findNextElement();

            // parse item variable
            if ( !$this->parseRequiredType( 'Variable', null, false ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
            }

            // skip whitespace and comments before the closing brace
            $this->findNextElement();
            $el->keyVariableName  = $el->itemVariableName;

            // Key Look up in the symbol table.
            if( !$this->parser->symbolTable->enter( $el->keyVariableName, ezcTemplateSymbolTable::VARIABLE, true ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, $this->parser->symbolTable->getErrorMessage() );
            }

            $el->itemVariableName = $this->lastParser->variableName;
        }
        
        // Value lookup in the symbol table.
        if( !$this->parser->symbolTable->enter( $el->itemVariableName, ezcTemplateSymbolTable::VARIABLE, true ) )
        {
            throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, $this->parser->symbolTable->getErrorMessage() );
        }

        // Check the cycle.
        while ( ($matchIncrement = $cursor->match ('increment' )) || $cursor->match ('decrement' ) )
        {
            $canBeArrow = false;

            do
            {
                $this->findNextElement();
                if ( !$this->parseOptionalType( 'Variable', null, false ) )
                {
                    throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
                }

                if( $matchIncrement ) $el->increment[] = $this->lastParser->element;
                else $el->decrement[] = $this->lastParser->element;


                $this->findNextElement();
            }
            while( $cursor->match( "," ) );
        }
 
        // Check the offset.
        if ( $cursor->match ('offset' ) )
        {
            $canBeArrow = false;
            $this->findNextElement();
            if ( !$this->parseOptionalType( 'Expression', null, false ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
            }

            $el->offset = $this->lastParser->rootOperator;
            $this->findNextElement();
        }
 
        // check for 'limit'.
        if ( $cursor->match ('limit' ) )
        {
            $canBeArrow = false;
            $this->findNextElement();
            if ( !$this->parseOptionalType( 'Expression', null, false ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
            }

            $el->limit = $this->lastParser->rootOperator;
            $this->findNextElement();
        }
 

        if ( !$this->parentParser->atEnd( $cursor, null, false ) )
        {
            throw new ezcTemplateSourceToTstParserException( $this, $cursor, 
                $canBeArrow ?  ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_ARROW_OR_CLOSE_CURLY_BRACKET :
                               ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE  );
        }

        $cursor->advance();

        $this->appendElement( $el );

        if ( $this->parser->debug )
        {
            echo "parsed foreach header:\n";
            var_dump( array(  'array' => $el->array, 'kv' => $el->keyVariableName, 'iv' => $el->itemVariableName  ) );
        }

        return true;
    }
}

?>
