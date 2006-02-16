<?php
/**
 * File containing the ezcTemplateOperatorAstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Represents PHP builtin types.
 *
 * Creating the type is done by simply passing the type to the constructor
 * which will take care of storing it and exporting it to PHP code later on.
 * The following types can be added:
 * - integer
 * - float
 * - boolean
 * - null
 * - string
 * - array
 * - objects
 *
 * The following types are not supported:
 * - resource
 *
 * @note Objects will have to implement the __set_state magic method to be
 *       properly exported.
 *
 * <code>
 * $tInt = new ezcTemplateOperatorAstNode( 5 );
 * $tFloat = new ezcTemplateOperatorAstNode( 5.2 );
 * $tString = new ezcTemplateOperatorAstNode( "a simple string" );
 * </code>
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
abstract class ezcTemplateOperatorAstNode extends ezcTemplateParameterizedAstNode
{
    /**
     * Constant for the number of parameters of unary operators, ie. 1.
     */
    const OPERATOR_TYPE_UNARY = 1;

    /**
     * Constant for the number of parameters of binary operators, ie. 2.
     */
    const OPERATOR_TYPE_BINARY = 2;

    /**
     * Constant for the number of parameters of ternary operators, ie. 3.
     */
    const OPERATOR_TYPE_TERNARY = 3;

    /**
     * Controls how unary operators are handled.
     * If true the operator is placed infront of the operand, if false it is
     * placed after the operand.
     */
    public $preOperator;

    /**
     * @param int $parameterCount The number of parameters the operator must have.
     * @param bool $preOperator Controls whether unary operators are placed before or after operand.
     * @todo Fix exception class + doc for it
     */
    public function __construct( $parameterCount, $preOperator = false )
    {
        parent::__construct( $parameterCount, $parameterCount );

        $this->preOperator = $preOperator;
    }

    /**
     * Returns the parameters of the operator.
     *
     * @note The values returned from this method must never be modified.
     * @return array(ezcTemplateAstNode)
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the parameters of the operator element.
     *
     * @note The values returned from this method must never be modified.
     *
     * @return array(ezcTemplateAstNode)
     */
    public function getSubElements()
    {
        return $this->parameters;
    }

    /**
     * @inheritdocs
     * @todo Fix exception class + doc for it
     */
    public function getRepresentation()
    {
        if ( count( $this->parameters ) < $this->minParameterCount )
            throw new Exception( "The operator contains only " . count( $this->parameters ) . " parameters but should at least have {$this->minParameterCount} parameters." );

        return "op '" . $this->getOperatorPHPSymbol() . "'";
    }

    /**
     * @inheritdocs
     * Calls visitUnaryOperator if it has one parameter, visitBinaryOperator() if it has two and visitTernaryOperator() if it has three.
     * All part of the ezcTemplateBasicAstNodeVisitor interface.
     * @todo Fix exception class
     */
    public function accept( ezcTemplateAstNodeVisitor $visitor )
    {
        $count = count( $this->parameters );
        if ( $count == 1 )
        {
            $visitor->visitUnaryOperator( $this );
        }
        else if ( $count == 2 )
        {
            $visitor->visitBinaryOperator( $this );
        }
        else if ( $count == 3 )
        {
            $visitor->visitTrinaryOperator( $this );
        }
        else
        {
            throw new Exception( "Operator can only have 1, 2 or 3 operands but this has {$count}" );
        }
    }

    /**
     * Returns a text string representing the PHP operator.
     * @return string
     */
    abstract public function getOperatorPHPSymbol();
}
?>