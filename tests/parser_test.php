<?php
/**
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogentag//
 * @filesource
 * @package Template
 * @subpackage Tests
 */

require_once "invariant_parse_cursor.php";

/**
 * @package Template
 * @subpackage Tests
 */
class ezcTemplateParserTest extends ezcMockCase
{
    public static function suite()
    {
         return new ezcTestSuite( "ezcTemplateParserTest" );
    }

    public function setUp()
    {
        //// required because of Reflection autoload bug
        class_exists( 'ezcTemplateSourceCode' );
        //class_exists( 'ezcTemplateManager' );
        $this->manager = new ezcTemplateManager();
        ezcMock::generate( 'ezcTemplateParser', array( "reportElementCursor" ), 'MockElement_ezcTemplateParser' );

        $this->basePath = realpath( dirname( __FILE__ ) ) . '/';
        $this->templatePath = $this->basePath . 'templates/';
        $this->templateCompiledPath = $this->basePath . 'compiled/';
        $this->templateStorePath = $this->basePath . 'stored_templates/';
    }

    public function tearDown()
    {
    }

    /**
     * Test parsing template code which does not contain any blocks.
     */
    public function testParsingTextElements()
    {
        $text = "abc def \nshow widget";
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        $items = array( array( 0, 1, 0,  20, 2, 11,  'TextBlock' ) );

        $this->setupExpectedPositions( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing template code which has a block with a missing end bracket.
     */
    public function testParsingBlockWithMissingBracketReportsError()
    {
        $text = "abc def \n{show widget";
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        $items = array( array( 0, 1, 0,  9, 2, 0,  'TextBlock' ) );

        $this->setupExpectedPositions( $parser, $text, $source, $items );

        try
        {
            $root = $parser->parseIntoTextElements();
            self::fail( "No parse exception thrown" );
        }
        catch( ezcTemplateSourceToTstParserException $e )
        {
        }

        $parser->verify();
    }

    /**
     * Test parsing template code contain all comment types.
     */
    public function testParsingAllCommentTypes()
    {
        self::assertThat( $this->templatePath . "comments_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "comments_test.tpl" );
        //$text = "abc def \n{show /*inside comment*/widget\n$w //eol comment\n}";
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        if ( $parser->debug )
            echo "\ncomments_test.tpl\n";

        $items = array( array( 'TextBlock' ),
                        array( 'DocComment', 'commentText', ' Documentation block ' ),
                        array( 'TextBlock' ),
                        array( 'Type' ),
                        array( 'BlockComment', 'commentText', 'inside comment' ),
                        array( 'PlusOperator' ),
                        array( 'EolComment', 'commentText', 'eol comment' ),
                        array( 'Type' ),
                        array( 'ExpressionBlock' ) );

        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing template code containing all builtin types except arrays.
     */
    public function testParseTypesExpression()
    {
        self::assertThat( $this->templatePath . "expression_types_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "expression_types_test.tpl" );
        //echo "\nexpression_types_test.tpl\n";
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        if ( $parser->debug )
            echo "\nexpression_types_test.tpl\n";

        $items = array( array( 'Type', 'value', 1 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 42 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', -1234 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1.0 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', -4.2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 0.5 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', "1" ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', "a short string" ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', "a short \"quoted\" string" ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', "1" ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', "a short string" ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', "a short \'quoted\' string" ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', true ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', false ),
                        array( 'ExpressionBlock' ) );

        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing template code containing array types.
     * @todo This currently fails, remove return statement when it is fixed.
     */
    public function testParsingArrayExpression()
    {
        self::assertThat( $this->templatePath . "expression_array_types_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "expression_array_types_test.tpl" );
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        if ( $parser->debug )
            echo "\nexpression_array_types_test.tpl\n";

        $items = array( array( 'Type', 'value', array() ),
                        array( 'ExpressionBlock' ) );

        $items_array1 = array( array( 'TextBlock' ),
                               array( 'Type', 'value', 1 ),
                               array( 'Type', 'value', 2 ),
                               array( 'Type', 'value', 3 ),
                               array( 'Type', 'value', array( 1, 2, 3 ) ),
                               array( 'ExpressionBlock' ) );

        $items = array_merge( $items,
                              $items_array1, $items_array1, $items_array1 );

        $items_array2 = array( array( 'TextBlock' ),
                               array( 'Type', 'value', 0 ),
                               array( 'Type', 'value', 1 ),
                               array( 'Type', 'value', 1 ),
                               array( 'Type', 'value', 2 ),
                               array( 'Type', 'value', 2 ),
                               array( 'Type', 'value', 3 ),
                               array( 'Type', 'value', array( 0 => 1, 1 => 2, 2 => 3 ) ),
                               array( 'ExpressionBlock' ) );

        $items = array_merge( $items,
                              $items_array2, $items_array2, $items_array2 );

        $items_array3 = array( array( 'TextBlock' ),
                               array( 'Type', 'value', "abc" ),
                               array( 'Type', 'value', "def" ),
                               array( 'Type', 'value', "foo" ),
                               array( 'Type', 'value', "bar" ),
                               array( 'Type', 'value', 5 ),
                               array( 'Type', 'value', "el1" ),
                               array( 'Type', 'value', "key1" ),
                               array( 'Type', 'value', -50 ),
                               array( 'Type', 'value', array( "abc" => "def", "foo" => "bar", 5 => "el1", "key1" => -50 ) ),
                               array( 'ExpressionBlock' ) );

        $items = array_merge( $items,
                              $items_array3, $items_array3, $items_array3 );

        $items_array4 = array( array( 'TextBlock' ),
                               array( 'Type', 'value', 3 ),
                               array( 'Type', 'value', array( 3 ) ),
                               array( 'Type', 'value', 2 ),
                               array( 'Type', 'value', array( array( 3 ), 2 ) ),
                               array( 'Type', 'value', 1 ),
                               array( 'Type', 'value', array( array( array( 3 ), 2 ), 1  ) ),
                               array( 'ExpressionBlock' ) );

        $items = array_merge( $items,
                              $items_array4 );

        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing template code containing variable statements.
     */
    public function testParsingVariablesExpression()
    {
        self::assertThat( $this->templatePath . "expression_variables_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "expression_variables_test.tpl" );
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        if ( $parser->debug )
            echo "\nexpression_variables_test.tpl\n";

        $items = array( array( 'Type', 'value', 'var' ),
                        array( 'Variable', 'name', 'var' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'varName' ),
                        array( 'Variable', 'name', 'varName' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'var_name' ),
                        array( 'Variable', 'name', 'var_name' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'var_0_name' ),
                        array( 'Variable', 'name', 'var_0_name' ),
                        array( 'ExpressionBlock' ) );

        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing template code containing operators expressions.
     */
    public function testParsingOperatorExpression()
    {
        self::assertThat( $this->templatePath . "expression_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "expression_test.tpl" );
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        if ( $parser->debug )
            echo "\nexpression_test.tpl\n";

        $items = array( array( 'Type', 'value', 'item' ),
                        array( 'Variable', 'name', 'item' ),
                        array( 'PropertyFetchOperator' ),
                        array( 'Type', 'value', 'prop1' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'item' ),
                        array( 'Variable', 'name', 'item' ),
                        array( 'PropertyFetchOperator' ),
                        array( 'Type', 'value', 0 ),
                        array( 'ExpressionBlock' ), // index 10

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'item' ),
                        array( 'Variable', 'name', 'item' ),
                        array( 'Type', 'value', 'prop1' ),
                        array( 'ArrayFetchOperator' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'item' ),
                        array( 'Variable', 'name', 'item' ),
                        array( 'Type', 'value', 0 ), // index 20
                        array( 'ArrayFetchOperator' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'item' ),
                        array( 'Variable', 'name', 'item' ),
                        array( 'Type', 'value', 42 ),
                        array( 'ArrayFetchOperator' ),
                        array( 'PropertyFetchOperator' ),
                        array( 'Type', 'value', 'subitem' ),
                        array( 'Type', 'value', "test" ), // index 30
                        array( 'ArrayFetchOperator' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'MultiplicationOperator' ), // index 40
                        array( 'Type', 'value', 2 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'MinusOperator' ),
                        array( 'Type', 'value', 4 ), // index 50
                        array( 'MultiplicationOperator' ),
                        array( 'Type', 'value', 6 ),
                        array( 'DivisionOperator' ),
                        array( 'Type', 'value', 'var' ),
                        array( 'Variable', 'name', 'var' ),
                        array( 'PropertyFetchOperator' ),
                        array( 'Type', 'value', 'lines' ),
                        array( 'Type', 'value', 0 ),
                        array( 'ArrayFetchOperator' ),
                        array( 'MinusOperator' ), // index 60
                        array( 'Type', 'value', 5 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 100 ),
                        array( 'ConcatOperator' ),
                        array( 'Type', 'value', "a" ),
                        array( 'ExpressionBlock' ) );

        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing operators with sub-expressions.
     */
    public function testParsingOperatorSubExpressions()
    {
        self::assertThat( $this->templatePath . "sub_expressions_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "sub_expressions_test.tpl" );
        //echo "\nsub_expressions_test.tpl\n";
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        $items = array( array( 'Type', 'value', 1 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'MultiplicationOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'MultiplicationOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'MinusOperator' ),
                        array( 'Type', 'value', 4 ),
                        array( 'DivisionOperator' ),
                        array( 'Type', 'value', 200 ),
                        array( 'MultiplicationOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),
                        array( 'ExpressionBlock' ),
                        array( 'ExpressionBlock' ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 5 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 'node' ),
                        array( 'Variable', 'name', 'node' ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 200 ),
                        array( 'ExpressionBlock' ),
                        array( 'ExpressionBlock' ) );

        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing all supported operators.
     */
    public function testParseOperators()
    {
        self::assertThat( $this->templatePath . "operators_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "operators_test.tpl" );
        $source = new ezcTemplateSourceCode( 'mock', 'mock', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        $items = array( array( 'Type', 'value', 'obj' ),
                        array( 'Variable', 'name', 'obj' ),
                        array( 'PropertyFetchOperator' ),
                        array( 'Type', 'value', 'prop' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'a' ),
                        array( 'Variable', 'name', 'a' ),
                        array( 'Type', 'value', 0 ),
                        array( 'ArrayFetchOperator' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'MinusOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'a' ),
                        array( 'ConcatOperator' ),
                        array( 'Type', 'value', 'b' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'MultiplicationOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'DivisionOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'ModuloOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'EqualOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'EqualOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'NotEqualOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'NotEqualOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'IdenticalOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'IdenticalOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'NotIdenticalOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'NotIdenticalOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'LessThanOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'LessThanOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'GreaterThanOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'GreaterThanOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'LessEqualOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'LessEqualOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'GreaterEqualOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'GreaterEqualOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'LogicalAndOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'LogicalAndOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'LogicalOrOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'LogicalOrOperator' ),
                        array( 'Type', 'value', 3 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'AssignmentOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PlusAssignmentOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'MinusAssignmentOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'MultiplicationAssignmentOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'DivisionAssignmentOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'ConcatAssignmentOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'ModuloAssignmentOperator' ),
                        array( 'Type', 'value', 2 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'PreIncrementOperator' ),
                        array( 'Type', 'value', 1 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'PreDecrementOperator' ),
                        array( 'Type', 'value', 1 ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PostIncrementOperator' ),
                        array( 'PlusOperator' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PostDecrementOperator' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PostIncrementOperator' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 1 ),
                        array( 'PostDecrementOperator' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'NegateOperator' ),
                        array( 'Type', 'value', 'a' ),
                        array( 'Variable', 'name', 'a' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'LogicalNegateOperator' ),
                        array( 'Type', 'value', 'a' ),
                        array( 'Variable', 'name', 'a' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'a' ),
                        array( 'Variable', 'name', 'a' ),
                        array( 'InstanceOfOperator' ),
                        array( 'Type', 'value', 'b' ),
                        array( 'Variable', 'name', 'b' ),
                        array( 'ExpressionBlock' ),

                        array( 'TextBlock' ),
                        array( 'Type', 'value', 'a' ),
                        array( 'Variable', 'name', 'a' ),
                        array( 'ConditionalOperator' ),
                        array( 'Type', 'value', 'b' ),
                        array( 'Variable', 'name', 'b' ),
                        array( 'ConditionalOperator' ),
                        array( 'Type', 'value', 'c' ),
                        array( 'Variable', 'name', 'c' ),
                        array( 'ExpressionBlock' ),

                        );

        if ( $parser->debug )
            echo "\noperators_test.tpl\n";

//        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing the literal block and escaped text portions.
     */
    public function testParsingLiteralBlock()
    {
        self::assertThat( $this->templatePath . "literal_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "literal_test.tpl" );
        $source = new ezcTemplateSourceCode( 'literal_test.tpl', 'mock:literal_test.tpl', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        if ( $parser->debug )
            echo "\nliteral_test.tpl\n";

        $items = array( array( 'TextBlock', 'originalText', "some plain text\n\n" ),
                        array( 'LiteralBlock', 'originalText', "{literal}\n\nno { code } inside here\nand \\{ escaped \\} braces are kept\n{/literal}" ),
                        array( 'TextBlock', 'originalText', "\n\nand \\no/ \{ code \} here either\n" ),
                        );

        $this->setupExpectedElements( $parser, $text, $source, $items );

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing the foreach block.
     */
    public function testParsingForeachBlock()
    {
        self::assertThat( $this->templatePath . "foreach_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "foreach_test.tpl" );
        $source = new ezcTemplateSourceCode( 'foreach_test.tpl', 'mock:foreach_test.tpl', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );
//        $parser->debug = true;

        if ( $parser->debug )
            echo "\nforeach_test.tpl\n";

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo "\n\n", $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing incorrect "foreach" blocks.
     */
    public function testParsingForeachBlock2()
    {
        $texts = array(
            '{foreach 1 as $i}{/foreach}',
            '{foreach $objects error $item}{/foreach}',
        );

        $nFailures = 0;
        foreach ( $texts as $i => $text )
        {
            $source = new ezcTemplateSourceCode( "while_test$i.tpl", "mock:while_test$i.tpl", $text );
            $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

            try
            {
                $root = $parser->parseIntoTextElements();
                if ( $parser->debug )
                    echo "\n\n", $root->outputTree(), "\n";
            }
            catch( Exception $e )
            {
                $nFailures++;
            }

            $parser->verify();

            unset( $parser );
            unset( $source );
        }

        $nOk = count( $texts ) - $nFailures;
        if ( $nOk > 0 )
            $this->fail( "Parser did not fail on $nOk incorrect templates foreach_test2" );
    }


    /**
     * Test parsing the "while" block.
     */
    public function testParsingWhileBlock()
    {
        self::assertThat( $this->templatePath . "while_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "while_test.tpl" );
        $source = new ezcTemplateSourceCode( 'while_test.tpl', 'mock:while_test.tpl', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );
        //$parser->debug = true;

        if ( $parser->debug )
            echo "\nwhile_test.tpl\n";

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo "\n\n", $root->outputTree(), "\n";

        $parser->verify();
    }

    /**
     * Test parsing incorrect do/while blocks.
     */
    public function testParsingWhileBlock2()
    {
        $texts = array(
            '{while}{/while}',
            '{do}{/do}',
            '{do}{/do while}',
            '{while}',
            '{while}{/do}',
            '{do}{/while}',
        );

        $nFailures = 0;
        foreach ( $texts as $i => $text )
        {
            $ok = true;
            $source = new ezcTemplateSourceCode( "while_test$i.tpl", "mock:while_test$i.tpl", $text );
            $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

            try
            {
                $root = $parser->parseIntoTextElements();
                if ( $parser->debug )
                    echo "\n\n", $root->outputTree(), "\n";
            }
            catch( Exception $e )
            {
                $nFailures++;
            }

            $parser->verify();

            unset( $parser );
            unset( $source );
        }

        $nOk = count( $texts ) - $nFailures;
        if ( $nOk > 0 )
            $this->fail( "Parser did not fail on $nOk incorrect templates while_test2" );
    }

    /**
     * Test parsing the "if" block.
     */
    public function testParsingIfBlock()
    {
        self::assertThat( $this->templatePath . "if_test.tpl", self::existsOnDisk() );

        $text = file_get_contents( $this->templatePath . "if_test.tpl" );
        $source = new ezcTemplateSourceCode( 'if_test.tpl', 'mock:if_test.tpl', $text );
        $parser = new MockElement_ezcTemplateParser( $source, $this->manager );

        //$parser->debug = true;

        if ( $parser->debug )
            echo "\nforeach_test.tpl\n";

        $root = $parser->parseIntoTextElements();

        if ( $parser->debug )
            echo "\n\n", $root->outputTree(), "\n";

        $parser->verify();

    }

    /**
     * Sets up expectations for reportElementCursor based on item list $items
     * which contains the start and ending position + expected class element.
     */
    public function setupExpectedElements( $parser, $text, $source, $items )
    {
        $index = 0;
        foreach( $items as $item )
        {
            $class = 'ezcTemplate' . $item[0];
            if ( substr( $class, -15 ) != 'OperatorTstNode' )
                $class .= 'TstNode';
            if ( isset( $item[1] ) && isset( $item[2] ) )
            {
                $parser->expects( self::at( $index ) )
                    ->method( "reportElementCursor" )
                    ->with( self::anything(),
                            self::anything(),
                            self::logicalAnd( self::isInstanceOf( $class ),
                                              self::hasProperty( $item[1] )->that( self::identicalTo( $item[2] ) ) ) );
            }
            else
            {
                $parser->expects( self::at( $index ) )
                    ->method( "reportElementCursor" )
                    ->with( self::anything(),
                            self::anything(),
                            self::logicalAnd( self::isInstanceOf( $class ) ) );
            }
            ++$index;
        }
        $parser->expects( self::exactly( $index ) )
            ->method( "reportElementCursor" )
            ->withAnyParameters();
    }

    /**
     * Sets up expectations for reportElementCursor based on item list $items
     * which contains the start and ending position + expected class element.
     */
    public function setupExpectedPositions( $parser, $text, $source, $items )
    {
        $index = 0;
        foreach( $items as $item )
        {
            $startCursor = new ezcTemplateCursor( $text, $item[0], $item[1], $item[2] );
            $endCursor = new ezcTemplateCursor( $text, $item[3], $item[4], $item[5] );
            $class = 'ezcTemplate' . $item[6];
            if ( substr( $class, -15 ) != 'OperatorTstNode' )
                $class .= 'TstNode';
            $element = new $class( $source, $startCursor, $endCursor );
            if ( isset( $item[7] ) && isset( $item[8] ) )
            {
                $property = $item[7];
                $element->$property = $item[8];
            }
            $parser->expects( self::at( $index ) )
                ->method( "reportElementCursor" )
                ->with( $startCursor, $endCursor, $element );
            $startCursor = $endCursor;
            ++$index;
        }
        $parser->expects( self::exactly( $index ) )
            ->method( "reportElementCursor" )
            ->withAnyParameters();
    }

    /**
     * Helper function to dump contents of an element.
     */
    static public function dumpElement( $element )
    {
        echo $element->outputTree(), "\n";
/*        echo "Element start {$element->startCursor->line}[{$element->startCursor->column}]:\n";
        if ( $element->startCursor->column > 0 )
            echo str_repeat( " ", $element->startCursor->column );
        echo $element->text();
        echo "Element end {$element->endCursor->line}[{$element->endCursor->column}]:\n";*/
    }
}

?>