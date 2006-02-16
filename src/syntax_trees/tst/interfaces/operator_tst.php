<?php
/**
 * File containing the ezcTemplateOperatorTstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Interface for operator elements in parser trees.
 *
 * This contains the required information to build a localized operator tree
 * based on operator precedence.
 *
 * === ===============  ====================
 * Lvl   Associativity    Operators
 * === ===============  ====================
 * 11  right            [ .(left)
 * 10  non-associative  ++ --
 *  9  non-associative  ! - instanceof
 *  8  left             * / %
 *  7  left             + - .
 *  6  non-associative  < <= > >=
 *  5  non-associative  == != === !==
 *  4  left             &&
 *  3  left             ||
 *  2  left             ? :
 *  1  right            = += -= *= /= .= %=
 * === ===============  ====================
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
abstract class ezcTemplateOperatorTstNode extends ezcTemplateInlineTstNode
{
    /**
     * The associativity order is not significant.
     */
    const NON_ASSOCIATIVE = 1;

    /**
     * The associativity order is left to right. This means the leftmost
     * parameter is evaluated first, then the next and so on.
     */
    const LEFT_ASSOCIATIVE = 2;

    /**
     * The associativity order is right to left.This means the rightmost
     * parameter is evaluated first, then the previous and so on.
     */
    const RIGHT_ASSOCIATIVE = 3;

     /**
     * List of parameters for the current operator, each entry is either another
     * operator, type, variable lookup or other parser element.
     * Each parameter that is an operator will have the $parentOperator member
     * set to this object.
     *
     * @var array(ezcTemplateTstNode)
     * @see prependParameter(), appendParameter(), getLastParameter(), setLastParameter(), mergeParameters()
     */
    public $parameters;

    /**
     * The operator element which has this as child (parameter) or null if no parent.
     *
     * @var ezcTemplateOperatorTstNode
     * @see getRoot(), outputTree()
     */
    public $parentOperator;

    /**
     * The precedence level for this operator.
     *
     * This determines which of two operator must be processed first and is
     * used to build the resulting operator tree.
     * The value starts at 1 (low precedence) to 12 (high).
     *
     * @var int
     * @see ezcTemplateParser::handleOperatorPrecedence(), outputTree()
     */
    public $precedence;

    /**
     * The precedence order for operators with same precedence level.
     *
     * This determines the correct precedence for operators having the exact
     * same precedence level.
     */
    public $order;

    /**
     * The associativity for the operator.
     *
     * This determines in which order parameters are processed, can be one of:
     * - NON_ASSOCIATIVE - The order is not significant.
     * - LEFT_ASSOCIATIVE - The order is left to right.
     * - RIGHT_ASSOCIATIVE - The order is right to left.
     */
    public $associativity;

    /**
     * Controls the maximum number of parameters the operator can handle.
     * This is either an integer or false which means there is no limit (the default).
     * @var int/false
     */
    public $maxParameterCount;

    /**
     * Initialize element with source and cursor positions.
     */
    public function __construct( ezcTemplateSourceCode $source, /*ezcTemplateCursor*/ $start, /*ezcTemplateCursor*/ $end,
                                 $precedence, $order, $associativity )
    {
        parent::__construct( $source, $start, $end );
        $this->precedence = $precedence;
        $this->order = $order;
        $this->associativity = $associativity;

        $this->parameters = array();
        $this->parentOperator = null;
        $this->maxParameterCount = false;
    }

    /**
     * Returns a symbol representation of the operator.
     * @return string
     */
    abstract public function symbol();

    /**
     * Prepends the element $element as a parameter to the current operator.
     * @param ezcTemplateTstNode
     */
    public function prependParameter( $element )
    {
        if ( !is_object( $element ) )
            throw new Exception( "Non-object <" . gettype( $element ) . "> add as parameter to <" . get_class( $this ) . ">" );
        $this->parameters = array_merge( array( $element ),
                                         $this->parameters );
    }

    /**
     * Appends the element $element as a parameter to the current operator.
     * @param ezcTemplateTstNode
     */
    public function appendParameter( $element )
    {
        if ( !is_object( $element ) )
            throw new Exception( "Non-object <" . gettype( $element ) . "> add as parameter to <" . get_class( $this ) . ">" );
        $this->parameters[] = $element;
    }

    /**
     * Returns the last parameter (if set) object of the current operator.
     * @return ezcTemplateTstNode
     */
    public function getLastParameter()
    {
        if ( count( $this->parameters ) > 0 )
            return $this->parameters[count( $this->parameters ) - 1];
        return null;
    }

    /**
     * Returns the number of parameters the operator has.
     * @return int
     */
    public function getParameterCount()
    {
        return count( $this->parameters );
    }

    /**
     * Overwrites the last parameter for the current operator to point to $element.
     * If there are no parameters it is simply appended to the list.
     */
    public function setLastParameter( ezcTemplateTstNode $parameter )
    {
        if ( count( $this->parameters ) > 0 )
            $this->parameters[count( $this->parameters ) - 1] = $parameter;
        else
            $this->parameters[] = $parameter;
    }

    /**
     * Removes the last parameter from the parameter list.
     */
    public function removeLastParameter()
    {
        if ( count( $this->parameters ) > 0 )
            unset( $this->parameters[count( $this->parameters ) - 1] );
    }

    /**
     * Copies all parameters from operator $operator into the parameter list
     * for the current operator.
     * @see canMergeParameters()
     */
    public function mergeParameters( ezcTemplateOperatorTstNode $operator )
    {
        foreach ( $operator->parameters as $parameter )
        {
            if ( $parameter instanceof ezcTemplateOperatorTstNode )
                $parameter->parentOperator = $this;
            $this->parameters[] = $parameter;
        }
    }

    /**
     * Checks if the current operator can merge parameters from the specificed
     * operator and returns true if it can.
     * If this is possible the two operators can be merged together into one
     * entity using mergeParameters().
     *
     * The default implementation allows this as long as it is the same class.
     *
     * @param ezcTemplateOperatorTstNode $operator
     * @return bool
     */
    public function canMergeParametersOf( ezcTemplateOperatorTstNode $operator )
    {
        return get_class( $operator ) == get_class( $this );
    }

    /**
     * Finds the top-most operator of the current expression tree and returns it.
     *
     * The top-most operator is found by checking the parent operator until there
     * are no more parent operators.
     *
     * @note If the current operator is the top-most operator $this is returned.
     * @return ezcTemplateOperatorTstNode
     */
    public function getRoot()
    {
        $operator = $this;
        while ( $operator->parentOperator !== null )
            $operator= $operator->parentOperator;
        return $operator;
    }

}
?>