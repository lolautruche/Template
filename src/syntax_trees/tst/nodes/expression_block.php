<?php
/**
 * File containing the ezcTemplateExpressionBlockTstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Block element containing an expression.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateExpressionBlockTstNode extends ezcTemplateTstNode
{
    /**
     * The bracket start character.
     * @var string
     */
    public $startBracket;

    /**
     * The bracket end character.
     * @var string
     */
    public $endBracket;

    /**
     * Array of all child elements.
     * @note Temporary compatability with $children
     *
     * @var array
     */
    public $elements;

    /**
     * The inline element starting the expression.
     *
     * @var ezcTemplateInlineTstNode
     */
    public $element;

    /**
     * The root of the parsed expression.
     */
    public $expressionRoot;

    /**
     *
     * @param ezcTemplateSource $source
     * @param ezcTemplateCursor $start
     * @param ezcTemplateCursor $end
     */
    public function __construct( ezcTemplateSourceCode $source, /*ezcTemplateCursor*/ $start, /*ezcTemplateCursor*/ $end )
    {
        parent::__construct( $source, $start, $end );
        $this->element = null;
        $this->startBracket = '{';
        $this->endBracket = '}';
        $this->expressionRoot = null;
    }

    public function symbol()
    {
        return $this->startBracket . 'expr' . $this->endBracket;
    }

    /**
     * Returns true since expression block elements can always be children of blocks.
     *
     * @return true
     */
    public function canBeChildOf( ezcTemplateBlockTstNode $block )
    {
        // Expression block elements can always be child of blocks
        return true;
    }

    /**
     * @inheritdocs
     * Returns the column of the starting cursor.
     */
    public function minimumWhitespaceColumn()
    {
        return $this->startCursor->column;
    }

}
?>
