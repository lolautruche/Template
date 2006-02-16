<?php
/**
 * File containing the ezcTemplateDynamicVariableAstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Represents PHP variables.
 *
 * Dynamic variables are code elements with one expression which defines the
 * name of the variable to find. The expression will be evaluated and the
 * return value of it will be used as variable name.
 *
 * Dynamic lookup of variable using other variable $some_var.
 * <code>
 * $var1 = new ezcTemplateVariableAstNode( 'some_var' );
 * $var2 = new ezcTemplateDynamicVariableAstNode( $var1 );
 * </code>
 * The corresponding PHP code will be:
 * <code>
 * ${$some_var}
 * </code>
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateDynamicVariableAstNode extends ezcTemplateAstNode
{
    /**
     * The expression which will, when evaluated, return the name of the
     * variable to use.
     * @var ezcTemplateAstNode
     */
    public $nameExpression;

    /**
     * @param ezcTemplateAstNode $expression The code element which will evaluate to the name of the variable.
     */
    public function __construct( ezcTemplateAstNode $nameExpression = null )
    {
        parent::__construct();
        $this->nameExpression = $nameExpression;
    }

    /**
     * Returns the variable name expression.
     *
     * @note The values returned from this method must never be modified.
     * @return array(ezcTemplateAstNode)
     */
    public function getSubElements()
    {
        return array( $this->nameExpression );
    }

    /**
     * @inheritdocs
     */
    public function getRepresentation()
    {
        return "dynamic-variable";
    }

    /**
     * @inheritdocs
     * Calls visitDynamicVariable() for ezcTemplateBasicAstNodeVisitor interfaces.
     */
    public function accept( ezcTemplateAstNodeVisitor $visitor )
    {
        if ( $this->nameExpression === null )
        {
            throw new Exception( "Dynamic variable element does not have the \$nameExpression variable set." );
        }
        $visitor->visitDynamicVariable( $this );
    }

}
?>