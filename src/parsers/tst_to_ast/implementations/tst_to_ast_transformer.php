<?php
/**
 * File containing the ezcTemplateAstNodeGenerator class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Transforms the TST tree to an AST tree.
 *
 * Implements the ezcTemplateTstNodeVisitor interface for visiting the nodes
 * and generating the appropriate ast nodes for them.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateTstToAstTransformer implements ezcTemplateTstNodeVisitor
{
    public $programNode = null;

    public $functions; 

    public function __construct()
    {
        $this->functions = new ezcTemplateFunctions();
    }

    public function __destruct()
    {
    }

    private function appendOperatorRecursively( ezcTemplateOperatorTstNode $type, ezcTemplateOperatorAstNode $astNode, $currentParameterNumber = 0)
    {
        $node = clone( $astNode );
        
        $node->appendParameter( $type->parameters[ $currentParameterNumber ]->accept( $this ) );

        $currentParameterNumber++;

        if( $currentParameterNumber == sizeof( $type->parameters ) - 1 ) 
        {
            // The last node.
            $node->appendParameter( $type->parameters[ $currentParameterNumber ]->accept( $this ) );
        }
        else
        {
            // More than two parameters, so repeat.
            $node->appendParameter( $this->appendOperatorRecursively( $type, $astNode, $currentParameterNumber ) );
        }

        return $node;
    }

    private function createBinaryOperatorAstNode( $type, ezcTemplateOperatorAstNode $astNode, $addParenthesis = true )
    {
        $astNode->appendParameter( $type->parameters[0]->accept( $this ));

        if( !isset( $type->parameters[1] ) )
        {
            // TODO>
            var_dump ($type );
        }

        $astNode->appendParameter( $type->parameters[1]->accept( $this ));

        return ( $addParenthesis ?  new ezcTemplateParenthesisAstNode( $astNode ) : $astNode );
    }

    private function createUnaryOperatorAstNode( $type, ezcTemplateOperatorAstNode $astNode, $addParenthesis = true )
    {
        $astNode->appendParameter( $type->parameters[0]->accept( $this ));

        return ( $addParenthesis ?  new ezcTemplateParenthesisAstNode( $astNode ) : $astNode );
    }
 
    private function isAssignmentNode( $astNode )
    {
        if ( $astNode instanceof ezcTemplateAssignmentOperatorAstNode ) return true;
        if ( $astNode instanceof ezcTemplateIncrementOperatorAstNode )  return true;
        if ( $astNode instanceof ezcTemplateDecrementOperatorAstNode )  return true;

        return false;
    }

    private function addOutputNodeIfNeeded( ezcTemplateAstNode $astNode )
    {
        if( $this->isAssignmentNode( $astNode ) ||  $astNode instanceof ezcTemplateStatementAstNode )
        {
            return $astNode;
        }

        return $this->assignToOutput( $astNode );
        //return new ezcTemplateEchoAstNode( array( $astNode ) );
    }

    private function createBody( array $elements )
    {
        $body = new ezcTemplateBodyAstNode();

        foreach( $elements as $element )
        {
            $astNode = $element->accept( $this );
            $body->appendStatement( $astNode );
        }

        return $body;
    }

    private function assignToOutput( $node )
    {
        return new ezcTemplateGenericStatementAstNode( new ezcTemplateConcatAssignmentOperatorAstNode( new ezcTemplateVariableAstNode( "_ezcTemplate_output" ), $node ) );
    }

    public function visitCustomBlockTstNode( ezcTemplateCustomBlockTstNode $type )
    {
        die("visitCustomTstNode");
    }

    public function visitProgramTstNode( ezcTemplateProgramTstNode $type )
    {
        if ( $this->programNode === null )
        {
            //$this->programNode = $this->createBody( $type->elements );
            $this->programNode = new ezcTemplateBodyAstNode();

            $cb = new ezcTemplateAstBuilder;
            $cb->assign( "_ezcTemplate_output", "" ); 
            $cb->call ("echo", $cb->variable( "_ezcTemplate_output" ) );
            $body = $cb->getAstNode();

            $this->programNode->appendStatement( $body->statements[0] );

            foreach( $type->elements as $element )
            {
                $astNode = $element->accept( $this );
                $this->programNode->appendStatement( $astNode );
            }


            $this->programNode->appendStatement( $body->statements[1] );
        }
        else
        {
            die ("PANIC, program node is not null ");
        }
    }

    public function visitLiteralBlockTstNode( ezcTemplateLiteralBlockTstNode $type )
    {
        return $this->assignToOutput( new ezcTemplateLiteralAstNode( $type->text ) );
        /*
        return new ezcTemplateGenericStatementAstNode( new ezcTemplateConcatAssignmentOperatorAstNode( new ezcTemplateVariableAstNode( "_ezcTemplate_output" ), new ezcTemplateLiteralAstNode( $type->text ) ) );
        //return new ezcTemplateEchoAstNode( array( new ezcTemplateLiteralAstNode( $type->text ) ) );
        */
    }

    public function visitEmptyBlockTstNode( ezcTemplateEmptyBlockTstNode $type )
    {
        return new ezcTemplateEolCommentAstNode( 'Result of empty block {}' );
    }

    public function visitParenthesisTstNode( ezcTemplateParenthesisTstNode $type )
    {
        $expression = $type->expressionRoot->accept( $this );
        return new ezcTemplateParenthesisAstNode( $expression );
    }

    public function visitOutputBlockTstNode( ezcTemplateOutputBlockTstNode $type )
    {
        $expression = $type->expressionRoot->accept( $this ); 
        $output = new ezcTemplateOutputAstNode( $expression );

        return $this->assignToOutput( $output );
        //return new ezcTemplateEchoAstNode( array( $expression ) );
    }

    public function visitModifyingBlockTstNode( ezcTemplateModifyingBlockTstNode $type )
    {
        $expression = $type->expressionRoot->accept( $this ); 
        return  new ezcTemplateGenericStatementAstNode( $expression );
    }

    public function visitLiteralTstNode( ezcTemplateLiteralTstNode $type )
    {
        return new ezcTemplateLiteralAstNode( $type->value );
    }

    public function visitIntegerTstNode( ezcTemplateIntegerTstNode $type )
    {
        die("visitIntegerTstNode");
    }

    public function visitVariableTstNode( ezcTemplateVariableTstNode $type )
    {
        return new ezcTemplateVariableAstNode( $type->name );
    }

    public function visitTextBlockTstNode( ezcTemplateTextBlockTstNode $type )
    {
        //$echo = new ezcTemplateOutputAstNode( $type->text );
        return $this->assignToOutput( new ezcTemplateLiteralAstNode( $type->text ) );

        //$echo = new ezcTemplateEchoAstNode( array( new ezcTemplateLiteralAstNode( $type->text ) ) );
        //return $echo;
    }

    public function visitFunctionCallTstNode( ezcTemplateFunctionCallTstNode $type )
    {
        $paramAst = array();
        foreach( $type->parameters as $parameter )
        {
            $paramAst[] = $parameter->accept( $this );
        }

        return $this->functions->getAstTree( $type->name, $paramAst );
    }

    public function visitDocCommentTstNode( ezcTemplateDocCommentTstNode $type )
    {
        return new ezcTemplateBlockCommentAstNode ( $type->commentText );
    }

    /** 
     * TST:
     * [Foreach]
     *  LiteralTstNode array
     *  string keyVariableName
     *  string itemVariableName
     *  array(Block) elements
     *
     * AST:
     * [Foreach]
     *  Expression arrayExpression
     *  ezcTemplateVariableAstNode keyVariable
     *  ezcTemplateVariableAstNode valueVariable
     *  Body statements
     */
    public function visitForeachLoopTstNode( ezcTemplateForeachLoopTstNode $type )
    {
        $astNode = new ezcTemplateForeachAstNode();

        $astNode->arrayExpression = $type->array->accept( $this );

        if( $type->keyVariableName  !== null )
        {
            $astNode->keyVariable = new ezcTemplateVariableAstNode( $type->keyVariableName );
        }

        $astNode->valueVariable = new ezcTemplateVariableAstNode( $type->itemVariableName );

        $astNode->body = $this->createBody( $type->elements );

        return $astNode;
    }

    public function visitWhileLoopTstNode( ezcTemplateWhileLoopTstNode $type )
    {
        if( $type->name == "do" )
        {
            $astNode = new ezcTemplateDoWhileAstNode();
        }
        else
        {
            $astNode = new ezcTemplateWhileAstNode();
        }

        $cb = new ezcTemplateConditionBodyAstNode();
        $cb->condition = $type->condition->accept( $this );
        $cb->body = $this->createBody( $type->elements );

        $astNode->conditionBody = $cb; 

        return $astNode;
    }

    public function visitIfConditionTstNode( ezcTemplateIfConditionTstNode $type )
    {
        $astNode = new ezcTemplateIfAstNode();

        // First condition, the 'if'.
        $if = new ezcTemplateConditionBodyAstNode();
        $if->condition = $type->condition->accept( $this );
        $if->body = $this->addOutputNodeIfNeeded( $type->elements[0]->accept( $this ) );
        $astNode->conditions[0] = $if;

        // Second condition, the 'elseif'.
        /*
        if( count( $type->elements ) == 3 )
        {
            $elseif = new ezcTemplateConditionBodyAstNode();
            $elseif->body = $this->addOutputNodeIfNeeded( $type->elements[1]->accept( $this ) );
            $astNode->conditions[1] = $else;

        }
        */

        if( isset( $type->elements[1] ) )
        {
            var_dump ( $type->condition );
            $else = new ezcTemplateConditionBodyAstNode();
            $else->body = $this->addOutputNodeIfNeeded( $type->elements[1]->accept( $this ) );
            $astNode->conditions[2] = $else;
        }

        return $astNode;
    }

    public function visitLoopTstNode( ezcTemplateLoopTstNode $type )
    {
        // STRANGE name, break, continue
        die ("visitLoopTstNode");
    }

    public function visitPropertyFetchOperatorTstNode( ezcTemplatePropertyFetchOperatorTstNode $type )
    {
        $astNode = new ezcTemplateObjectAccessOperatorAstNode();
        $astNode->appendParameter( $type->parameters[0]->accept( $this ));
        $astNode->appendParameter( new ezcTemplateCurlyBracesAstNode( $type->parameters[1]->accept( $this ) ) );

        return $astNode;

    }

    public function visitArrayFetchOperatorTstNode( ezcTemplateArrayFetchOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateArrayFetchOperatorAstNode() );
    }

    // return ezcTemplateTstNode;
    public function visitPlusOperatorTstNode( ezcTemplatePlusOperatorTstNode $type )
    {
        return new ezcTemplateParenthesisAstNode( $this->appendOperatorRecursively( $type, new ezcTemplateAdditionOperatorAstNode) );
    }

    public function visitMinusOperatorTstNode( ezcTemplateMinusOperatorTstNode $type )
    {
        return new ezcTemplateParenthesisAstNode($this->appendOperatorRecursively( $type, new ezcTemplateSubtractionOperatorAstNode) );
    }

    public function visitConcatOperatorTstNode( ezcTemplateConcatOperatorTstNode $type )
    {
        return new ezcTemplateParenthesisAstNode($this->appendOperatorRecursively( $type, new ezcTemplateConcatOperatorAstNode) );
    }

    public function visitMultiplicationOperatorTstNode( ezcTemplateMultiplicationOperatorTstNode $type )
    {
        return new ezcTemplateParenthesisAstNode($this->appendOperatorRecursively( $type, new ezcTemplateMultiplicationOperatorAstNode) );
    }

    public function visitDivisionOperatorTstNode( ezcTemplateDivisionOperatorTstNode $type )
    {
        return new ezcTemplateParenthesisAstNode($this->appendOperatorRecursively( $type, new ezcTemplateDivisionOperatorAstNode) );
    }

    public function visitModuloOperatorTstNode( ezcTemplateModuloOperatorTstNode $type )
    {
        return new ezcTemplateParenthesisAstNode($this->appendOperatorRecursively( $type, new ezcTemplateModulusOperatorAstNode) );
    }

    public function visitEqualOperatorTstNode( ezcTemplateEqualOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateEqualOperatorAstNode() );
    }

    public function visitNotEqualOperatorTstNode( ezcTemplateNotEqualOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateNotEqualOperatorAstNode() );
    }

    public function visitIdenticalOperatorTstNode( ezcTemplateIdenticalOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateIdenticalOperatorAstNode() );
    }

    public function visitNotIdenticalOperatorTstNode( ezcTemplateNotIdenticalOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateNotIdenticalOperatorAstNode() );
    }

    public function visitLessThanOperatorTstNode( ezcTemplateLessThanOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateLessThanOperatorAstNode() );
    }

    public function visitGreaterThanOperatorTstNode( ezcTemplateGreaterThanOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateGreaterThanOperatorAstNode() );
    }

    public function visitLessEqualOperatorTstNode( ezcTemplateLessEqualOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateLessEqualOperatorAstNode() );
    }

    public function visitGreaterEqualOperatorTstNode( ezcTemplateGreaterEqualOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateGreaterEqualOperatorAstNode() );
    }

    public function visitLogicalAndOperatorTstNode( ezcTemplateLogicalAndOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateLogicalAndOperatorAstNode() );
    }

    public function visitLogicalOrOperatorTstNode( ezcTemplateLogicalOrOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateLogicalOrOperatorAstNode() );
    }

    public function visitAssignmentOperatorTstNode( ezcTemplateAssignmentOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateAssignmentOperatorAstNode(), false );
    }

    public function visitPlusAssignmentOperatorTstNode( ezcTemplatePlusAssignmentOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateAdditionAssignmentOperatorAstNode(), false );
    }

    public function visitMinusAssignmentOperatorTstNode( ezcTemplateMinusAssignmentOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateSubtractionAssignmentOperatorAstNode(), false );
    }

    public function visitMultiplicationAssignmentOperatorTstNode( ezcTemplateMultiplicationAssignmentOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateMultiplicationAssignmentOperatorAstNode(), false );
    }

    public function visitDivisionAssignmentOperatorTstNode( ezcTemplateDivisionAssignmentOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateDivisionAssignmentOperatorAstNode(), false );
    }

    public function visitConcatAssignmentOperatorTstNode( ezcTemplateConcatAssignmentOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateConcatAssignmentOperatorAstNode(), false );
    }

    public function visitModuloAssignmentOperatorTstNode( ezcTemplateModuloAssignmentOperatorTstNode $type )
    {
        return $this->createBinaryOperatorAstNode( $type, new ezcTemplateModulusAssignmentOperatorAstNode(), false );
    }

    public function visitPreIncrementOperatorTstNode( ezcTemplatePreIncrementOperatorTstNode $type )
    {
        // Pre increment has the parameter in the constructor set to true.
        return $this->createUnaryOperatorAstNode( $type, new ezcTemplateIncrementOperatorAstNode( true ), false );
    }

    public function visitPreDecrementOperatorTstNode( ezcTemplatePreDecrementOperatorTstNode $type )
    {
        // Pre increment has the parameter in the constructor set to false.
        return $this->createUnaryOperatorAstNode( $type, new ezcTemplateDecrementOperatorAstNode( true ), false );
    }

    public function visitPostIncrementOperatorTstNode( ezcTemplatePostIncrementOperatorTstNode $type )
    {
        // Post increment has the parameter in the constructor set to false.
        return $this->createUnaryOperatorAstNode( $type, new ezcTemplateIncrementOperatorAstNode( false ), false );
    }

    public function visitPostDecrementOperatorTstNode( ezcTemplatePostDecrementOperatorTstNode $type )
    {
        // Post increment has the parameter in the constructor set to false.
        return $this->createUnaryOperatorAstNode( $type, new ezcTemplateDecrementOperatorAstNode( false ), false );
    }

    public function visitNegateOperatorTstNode( ezcTemplateNegateOperatorTstNode $type )
    {
        // Is the minus.
        return $this->createUnaryOperatorAstNode( $type, new ezcTemplateArithmeticNegationOperatorAstNode(), true );
    }

    public function visitLogicalNegateOperatorTstNode( ezcTemplateLogicalNegateOperatorTstNode $type )
    {
        return $this->createUnaryOperatorAstNode( $type, new ezcTemplateLogicalNegationOperatorAstNode(), true );
    }

    public function visitInstanceOfOperatorTstNode( ezcTemplateInstanceOfOperatorTstNode $type )
    {
        die ("visitInstanceOfOperatorTstNode");
    }

    public function visitBlockCommentTstNode( ezcTemplateBlockCommentTstNode $type )
    {
        die("The visitBlockCommentTstNode is called, however this node shouldn't be in the TST tree. It's used for testing purposes.");
    }

    public function visitEolCommentTstNode( ezcTemplateEolCommentTstNode $type )
    {
        die("The visitEolCommentTstNode is called, however this node shouldn't be in the TST tree. It's used for testing purposes.");
    }

    public function visitBlockTstNode( ezcTemplateBlockTstNode $type ) 
    {
        // Used abstract, but is parsed. Unknown.
        die("visitBlockTstNode");
    }

    public function visitDeclarationTstNode( ezcTemplateBlockTstNode $type ) 
    {
        $expression = $type->expression === null ? new ezcTemplateConstantAstNode( "NULL") : $type->expression->accept($this);
        return new ezcTemplateGenericStatementAstNode( new ezcTemplateAssignmentOperatorAstNode( $type->variable->accept($this), $expression ) );
    }

    public function visitArrayRangeOperatorTstNode( ezcTemplateArrayRangeOperatorTstNode $type ) 
    {
        $paramAst = array();
        foreach( $type->parameters as $parameter )
        {
            $paramAst[] = $parameter->accept( $this );
        }

        return $this->functions->getAstTree( "array_fill_range", $paramAst );
    }



}
?>
