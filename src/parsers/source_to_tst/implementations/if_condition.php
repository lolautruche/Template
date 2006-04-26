<?php
/**
 * File containing the ezcTemplateIfConditionSourceToTstParser class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */
/**
 * Parser for {if} control structure.
 *
 * Parses inside the blocks {...} and looks for an expression by using the
 * ezcTemplateExpressionSourceToTstParser class.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateIfConditionSourceToTstParser extends ezcTemplateSourceToTstParser
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
        $name = $this->block->name;

        $this->status = self::PARSE_PARTIAL_SUCCESS;

        // handle closing block
        if ( $this->block->isClosingBlock )
        {
            if ( $this->parser->debug )
                echo "Starting end of \"if\"\n";

            // skip whitespace and comments
            $this->findNextElement();
            
            if( !$cursor->match( '}' ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            $el = $this->parser->createIfCondition( $this->startCursor, $cursor );
            $el->name = 'if';
            $el->isClosingBlock = true;
            $this->appendElement( $el );
            return true;
        }

        $condition = null;

        $this->findNextElement();

        if( $name != 'else' ) // Parse condition
        {
            if ( !$this->parseRequiredType( 'Expression', null, false ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
            }

            $condition = $this->lastParser->rootOperator;
            $this->findNextElement();
        }

        if( !$cursor->match( '}' ) )
        {
            throw new ezcTemplateSourceToTstParserException( $this, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
        }

        $cb = $this->parser->createConditionBody( $this->startCursor, $cursor );
        $cb->condition = $condition;

        if ($name == 'if' )
        {
            $el = $this->parser->createIfCondition( $this->startCursor, $cursor );
            $el->children[] = $cb;
            $el->name = 'if';
            $this->appendElement( $el );
        }
        else
        {
            $this->appendElement( $cb );
        }

        return true;
    }
}

?>
