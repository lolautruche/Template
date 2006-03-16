<?php
/**
 * File containing the ezcTemplateModifyingBlockTstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Block element containing an modifying expression.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateModifyingBlockTstNode extends ezcTemplateBlockTstNode
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
     * The inline element starting the modifying expression.
     *
     * @var ezcTemplateInlineTstNode
     */
//    public $element; // removed, not needed

    /**
     * The root of the parsed modifying expression.
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
//        $this->element = null; // removed, not needed
        $this->startBracket = '{';
        $this->endBracket = '}';
        $this->expressionRoot = null;
    }

    /**
     * Returns true since modifying expression block elements can always be children of blocks.
     *
     * @return true
     */
    public function canBeChildOf( ezcTemplateBlockTstNode $block )
    {
        // Modifying expression block elements can always be child of blocks
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

    /**
     * @return true if the block is considered an output block.
     */
    public function isOutput()
    {
        if ( $this->expressionRoot instanceof ezcTemplateAssignmentOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplateAssignmentOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplateConcatAssignmentOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplateDivisionAssignmentOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplateMinusAssignmentOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplateModuloAssignmentOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplateMultiplicationAssignmentOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplatePlusAssignmentOperatorTstNode )
            return false;

        if ( $this->expressionRoot instanceof ezcTemplatePostIncrementOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplatePreIncrementOperatorTstNode )
            return false;

        if ( $this->expressionRoot instanceof ezcTemplatePostDecrementOperatorTstNode )
            return false;
        if ( $this->expressionRoot instanceof ezcTemplatePreDecrementOperatorTstNode )
            return false;

        return true;
    }
}
?>
