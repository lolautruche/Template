<?php
/**
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogentag//
 * @filesource
 * @package Template
 * @subpackage Tests
 */

/**
 * @package Template
 * @subpackage Tests
 */
class ezcTemplateCompiledCodeTest extends ezcTestCase
{
    public static function suite()
    {
         return new ezcTestSuite( "ezcTemplateCompiledCodeTest" );
    }

    public function setUp()
    {
        $this->basePath = realpath( dirname( __FILE__ ) ) . '/';
        $this->templatePath = $this->basePath . 'templates/';
        $this->templateCompiledPath = $this->basePath . 'compiled/';
        $this->templateStorePath = $this->basePath . 'stored_templates/';

        // the compiled file should exist
        self::assertTrue( file_exists( $this->templateCompiledPath . "full-14862b79ceaf01443626bd5d564c53e2.php" ),
                          "Compiled file <" . $this->templateCompiledPath . "full-14862b79ceaf01443626bd5d564c53e2.php> is missing." );

        // remove temporarily stored file if possible
        if ( file_exists( $this->templateStorePath . "full-14862b79ceaf01443626bd5d564c53e2.php" ) )
            unlink( $this->templateStorePath . "full-14862b79ceaf01443626bd5d564c53e2.php" );
    }

    public function tearDown()
    {
        // remove temporarily stored file if possible
        if ( file_exists( $this->templateStorePath . "full-14862b79ceaf01443626bd5d564c53e2.php" ) )
            unlink( $this->templateStorePath . "full-14862b79ceaf01443626bd5d564c53e2.php" );
    }

    public function testDefault()
    {
        $conf = new ezcTemplateCompiledCode( '8efb', $this->templateCompiledPath . '8efb.php' );

        self::assertPropertySame( $conf, 'identifier', "8efb" );
        self::assertPropertySame( $conf, 'path',       $this->templateCompiledPath . '8efb.php' );
        self::assertPropertySame( $conf, 'context',    null );
        self::assertPropertySame( $conf, 'manager',    null );
    }

    /**
     * Check if the path to the compiled file is correct with findCompiled().
     * It runs tests for multiple template files which the path is known beforehand.
     */
    public function testFindCompiled()
    {
        $templates = array( array( 'zhadum.tpl',
                                   $this->templateCompiledPath . '/xhtml-updqr0/zhadum-7174546a39a618c4ab516f73491bcfb6.php' ),
//                            7174546a39a618c4ab516f73491bcfb6
//                            1624u8nd60pw0g8
                            array( 'pagelayout.tpl',
                                   $this->templateCompiledPath . '/xhtml-updqr0/pagelayout-0cbe34d0b50c894ad9a892728c1b43f7.php' ) );
//        0cbe34d0b50c894ad9a892728c1b43f7
//        pvbvhkpzrnkw

        foreach ( $templates as $templateData )
        {
            $template = $templateData[0];
            $path = $templateData[1];

            $context = new ezcTemplateXhtmlContext();
            $manager = new ezcTemplateManager();
            $manager->configuration = new ezcTemplateConfiguration( $this->templatePath, $this->templateCompiledPath );
            $code = ezcTemplateCompiledCode::findCompiled( $template,
                                                           $context,
                                                           $manager );
            self::assertSame( $path,
                              $code->path,
                              "Total path element does not match expected value." );
        }
    }

    public function testExecuteNoExists()
    {
        $conf = new ezcTemplateCompiledCode( '8efb', $this->templateCompiledPath . '8efb.php' );
        self::assertTrue( !file_exists( $conf->path ), "Compiled file <" . $conf->path . "> should not exist." );
        self::assertSame( false, $conf->isAvailable(), "isAvailable() should return false." );
    }

    public function testExecuteNotReadable()
    {
        // If running as root you can always write, so this test should be
        // skipped when running as root.
        if ( posix_getuid() == 0 )
        {
            return;
        }
        copy( $this->templateCompiledPath . "full-14862b79ceaf01443626bd5d564c53e2.php",
              $this->templateStorePath . "full-14862b79ceaf01443626bd5d564c53e2.php" );

        // This only works on Linux/Unix, what to do here on other platforms?
        $old = umask( 0 );
        chmod( $this->templateStorePath . "full-14862b79ceaf01443626bd5d564c53e2.php", 0222 );
        umask( $old );
        $conf = new ezcTemplateCompiledCode( '14862b79ceaf01443626bd5d564c53e2',
                                             $this->templateStorePath . 'full-14862b79ceaf01443626bd5d564c53e2.php' );
        self::assertTrue( file_exists( $conf->path ), "Compiled file <" . $conf->path . "> should exist." );
        self::assertSame( false, $conf->isValid(), "isValid() should return false." );
    }

    public function testExecuteNoManager()
    {
        $conf = new ezcTemplateCompiledCode( '8efb', $this->templateCompiledPath . '8efb.php' );

        $result = new ezcTemplateExecutionResult();
        try
        {
            $conf->execute( $result );
            self::fail( "No exception thrown when manager is missing" );
        }
        catch( ezcTemplateNoManagerException $e )
        {
        }
    }

    public function testExecuteNoContext()
    {
        $conf = new ezcTemplateCompiledCode( '8efb', $this->templateCompiledPath . '8efb.php' );
        $conf->manager = new ezcTemplateManager();

        $result = new ezcTemplateExecutionResult();
        try
        {
            $conf->execute( $result );
            self::fail( "No exception thrown when context is missing" );
        }
        catch( ezcTemplateNoOutputContextException $e )
        {
        }
    }

    public function testExecuteInvalidCompiled()
    {
        $conf = new ezcTemplateCompiledCode( '8efb', $this->templateCompiledPath . '8efb.php' );
        $conf->manager = new ezcTemplateManager();
        $conf->context = new ezcTemplateXhtmlContext();

        $result = new ezcTemplateExecutionResult();
        try
        {
            $conf->execute( $result );
            self::fail( "No exception thrown when context is missing" );
        }
        catch( ezcTemplateInvalidCompiledFileException $e )
        {
        }
    }

    public function testExecute()
    {
        $conf = new ezcTemplateCompiledCode( '14862b79ceaf01443626bd5d564c53e2',
                                             $this->templateCompiledPath . 'full-14862b79ceaf01443626bd5d564c53e2.php' );
        $conf->manager = new ezcTemplateManager();
        $conf->context = new ezcTemplateXhtmlContext();

        $result = new ezcTemplateExecutionResult();
        $conf->execute( $result );
    }
}

?>