<?php
/**
 * File containing the ezcTemplateFunctions class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */

/**
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateFunctions
{
    protected $functionToClass;
    protected $errorMessage = "";

    public function __construct()
    {
        $this->functionToClass = include( "function_to_class.php" );
    }

    protected static function functionCall( $name, $parameters )
    {
        return array( "ezcTemplateFunctionCallAstNode", array( $name, array( "_array", $parameters ) ) );
    }

    protected static function value( $val )
    {
        return array( "ezcTemplateLiteralAstNode", array( $val ) );
    }

    protected static function constant( $val )
    {
        return array( "ezcTemplateConstantAstNode", array( $val ) );
    }

    protected static function concat( $left, $right )
    {
        return array( "ezcTemplateConcatOperatorAstNode", array( $left, $right ) );
    }
    
    protected static function isSubstitution( $parameter )
    {
        return (strpos( $parameter, "%" ) !== false );
    }

    protected static function isOptional( $parameter )
    {
        return $parameter[0] == "["; 
    }

    protected static function isMany( $parameter )
    {
        return ( $parameter[1] == "." && $parameter[2] == "." );
    }   

    protected static function countParameters( $parameters )
    {
        $total = sizeof( $parameters );
        if( isset( $parameterMap[ "__rest__" ] ) )
        {
            $total += sizeof( $parametersMap["__rest__"] ) - 1;
        }

        return $total;
    }

    protected function createObject( $className, $functionParameters )
    {
        if( $className == "_array" )
        {
            return $functionParameters;
        }

        return call_user_func_array(
            array( new ReflectionClass( $className ), 'newInstance' ), $functionParameters );
    }
 
    protected function processAst( $functionName, $struct, $parameterMap, &$usedRest )
    {
        $pOut = array();
        foreach( $struct[1] as $pIn )
        {
            if( is_array( $pIn ) )
            {
                $pOut[] =  $this->processAst( $functionName, $pIn, $parameterMap, $usedRest );
            }
            else
            {
                if( self::isSubstitution( $pIn ) )
                {
                    // Skip the optional parameter is the value is NULL.
                    if( $parameterMap[$pIn] !== null )
                    {
                        $pOut[] = $parameterMap[ $pIn ];

                        if( self::isMany( $pIn ) && isset( $parameterMap[ "__rest__" ]  ) )
                        {
                            $usedRest = true;
                            foreach( $parameterMap[ "__rest__" ] as $restParameter )
                            {
                                $pOut[] = $restParameter;
                            }
                        }
                    }
                    elseif( !self::isOptional( $pIn ) )
                    {
                        throw new Exception( sprintf( ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_PARAMETER, $functionName, $pIn ) );
                    }
                }
                else 
                {
                    // No substitution needed. 
                    $pOut[] = $pIn;
                }
            }
        }
        return $this->createObject( $struct[0], $pOut );
    }

    protected function createAstNodes( $functionName, $functionDefinition, $processedParameters )
    {
        $index = sizeof( $functionDefinition ) == 3  ? 1 : 0;

        $realParameters = sizeof( $processedParameters );
        $definedParameters = self::countParameters( $functionDefinition[ $index ] );


        // Map the parameters from the function definition to the given (real) parameters.
        $parameterMap = array();
        $i = 0;
        foreach( $functionDefinition[ $index ] as $p )
        {
            if( self::isOptional( $p ) && $realParameters < $definedParameters)
            {
                // We should skip this parameter.
                $parameterMap[$p] = null;
            }
            else
            {
                $parameterMap[$p] = isset( $processedParameters[$i] ) ? $processedParameters[$i] : null;
                $i++;
            }
        }

        // Remaining parameters are stored in the "__rest__".
        $hasRest = false;
        while( isset( $processedParameters[$i] ) )
        {
            $parameterMap[ "__rest__" ][] = $processedParameters[$i++];
            $hasRest = true;
        }

        $usedRest = false;
        $ast = $this->processAst( $functionName, $functionDefinition[ $index + 1 ], $parameterMap, $usedRest );

        if( $hasRest && !$usedRest )
        {
            throw new Exception( sprintf( ezcTemplateSourceToTstErrorMessages::MSG_TOO_MANY_PARAMETERS, $functionName ) );
        }


        if( sizeof( $functionDefinition ) == 3 )
        {
            $ast->typeHint = $functionDefinition[0];
        }
        else
        {
            $ast->typeHint = ezcTemplateAstNode::TYPE_ARRAY | ezcTemplateAstNode::TYPE_VALUE; 
        }
 
        return new ezcTemplateParenthesisAstNode( $ast );
    }

    public function getClass( $functionName )
    {
        foreach ($this->functionToClass as $func => $class )
        {
            if( preg_match( $func, $functionName ) )
            {
                return $class;
            }
        }

        return null;
    }

    public function getAstTree( $functionName, $parameters )
    {
        $this->errorMessage = "";
        $class = $this->getClass( $functionName );

        if( $class !== null )
        {
            $f = call_user_func( array( $class, "getFunctionSubstitution"), $functionName, $parameters );
            if( $f !== null ) return $this->createAstNodes( $functionName, $f, $parameters );
        }

        throw new Exception( sprintf( ezcTemplateSourceToTstErrorMessages::MSG_UNKNOWN_FUNCTION, $functionName ) );

            // Try to find a manual function.

            // Return the AST nodes.
            //return $this->createAstNodes( $f, $parameters );

/*        
            // Reorder parameters if needed.
            $params = $this->fixParameters( $parameters, $f[1], isset( $f[2] ) ? $f[2] : null );
*/
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public static function getFunctionSubstitution( $functionName, $parameters )
    {
        die( "Subclasses need to implement the getFunctionSubstitution method." );
    }
}
?>
