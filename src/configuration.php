<?php
/**
 * File containing the ezcTemplateConfiguration class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Contains the common configuration options for template managers.
 *
 * When the configuration is first initialized it will only contain loaders for
 * the builtin functions, blocks and resource handler. If more are wanted they
 * must be registered with the registerAutoloader() function.
 * <code>
 * $conf->registerAutoloader( new ezcTemplateAutoloaderDefinition(
 * "path/to/loader.php", "TestLoader" ) );
 * </code>
 *
 * The currently registered list can be accessed with getRegisteredAutoloaders()
 * and initializing all of them is possible with setRegisteredAutoloaders().
 *
 * The resource locators are also maintained by the configuration, they are used
 * to figure out the real location of a template based from a resource string. To
 * register a new locator use the registerResourceLocator() method, it needs an
 * object which implements the interface ezcTemplateResourceLocator. Accessing
 * registered locators are done with getRegisteredResourceLocator() or accessing
 * the $resourceLocators member variable directly.
 *
 * To get a unique instance of a configuration use the getInstance() static
 * function, it will return an instance for the given name. Setting a new
 * instance can be done with setInstance().
 *
 * Whenever a template source or compiled code is accessed it will use the
 * $templatePath and $compiledPath respectively as the base path. The full path
 * is generated by using the value of theses variables and then appending a slash
 * (/) and the subpath, this means it is possible to  have the templates in the
 * root of the filesystem by setting an empty string or a string starting with a
 * slash. For instance:
 * <code>
 * // accessing templates in /usr/share and compile them in /var/cache
 * $conf->templatePath = "/usr/share/eztemplate";
 * $conf->compiledPath = "/var/cache/eztemplate";
 * </code>
 *
 * Accessing templates from the applications directory is done with a single dot
 * (.), this is also the default values.
 * <code>
 * // uses current directory for accessing templates and compiling them
 * $conf->templatePath = ".";
 * $conf->compiledPath = ".";
 * </code>
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateConfiguration
{

    /**
     * An array of ezcTemplateAutoload objects which are used to load template
     * elements such as functions and blocks.
     *
     * @note If it contains false the system will initialize the loaders from the
     * $autoloadDefinitions variable.
     * @var array
     * @note __get property
     */
    // private $autoloadList = false;

    /**
     * An array of ezcTemplateAutoloaderDefinition objects which are used to define
     * where the autoload classes can be found.
     *
     * This array contains the minium information needed when the template system is
     * first initialised, the rest of the autoloader classes are only loaded when
     * actually needed.
     *
     * @note Use registerAutoloader() or setRegisteredAutoloaders to modify this
     * variable.
     *
     * @var array
     * @note __get/__set property
     */
    // private $autoloadDefinitions;

    /**
     * Registered resource locator (ezcTemplateResourceLocato) in the manager. They
     * are used to find source and compiled code.
     * @var array
     * @note __get/__set property
     */
    // public $resourceLocators;

    /**
     * The base path for all the source templates. e.g. 'design' or 'templates'
     * @var string
     */
    public $templatePath = ".";

    /**
     * The base path for all the compiled templates. e.g. 'var/template/compiled'
     * @var string
     */
    public $compiledPath = ".";

    /**
     * List of global instances, looked up using the identifier string.
     */
    static private $instanceList = array();

    /**
     * An array containing the properties of this object.
     * autoloadList - An array of ezcTemplateAutoload objects which are used to load
     * template elements such as functions and blocks.
     *
     * autoloadDefinitions - An array of ezcTemplateAutoloaderDefinition objects
     * which are used to define where the autoload classes can be found.
     *
     * resourceLocators - Registered resource locator (ezcTemplateResourceLocato) in
     * the manager. They are used to find source and compiled code.
     */
    private $properties = array( 'autoloadList' => false,
                                 'autoloadDefinitions' => array(),
                                 'resourceLocators' => array() );

    /**
     * Property get
     */
    public function __get( $name )
    {
        switch( $name )
        {
            case 'autoloadList':
            case 'autoloadDefinitions':
            case 'resourceLocators':
                return $this->properties[$name];
            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }

    /**
     * Property set
     */
    public function __set( $name, $value )
    {
        switch( $name )
        {
            case 'autoloadDefinitions':
                if ( !is_array( $value ) )
                     throw new ezcBaseValueException( $name, $value, 'array' );
                $this->properties[$name] = $value;
                break;
            case 'resourceLocators':
                if ( !is_array( $value ) )
                     throw new ezcBaseValueException( $name, $value, 'array' );
                foreach( $value as $id => $locator )
                {
                    if ( !( $locator instanceof ezcTemplateContext ) )
                        throw new ezcBaseValueException( "name[$id]", $locator, 'ezcTemplateContext' );
                }
                $this->properties[$name] = $value;
                break;
            case 'autoloadList':
                throw new ezcBasePropertyPermissionException( $name, ezcBasePropertyPermissionException::READ );
            default:
                throw new ezcBasePropertyNotFoundException( $name );
        }
    }

    /**
     * Property isset
     */
    public function __isset( $name )
    {
        switch( $name )
        {
            case 'autoloadList':
            case 'autoloadDefinitions':
            case 'resourceLocators':
                return true;
            default:
                return false;
        }
    }

    /**
     * Initialises the configuration with default template and compiled path.
     *
     * @param string $templatePath The default template path, all requested templates
     * are search from here. Use an empty string to fetch templates from the root of
     * the filesystem.
     * @param string $compiledPath The default template path, all compiled templates
     * are placed here using subfolders. Use an empty string to compile templates at
     * the root of the filesystem.
     */
    public function __construct( $templatePath = ".", $compiledPath = "." )
    {
        $this->templatePath = $templatePath;
        $this->compiledPath = $compiledPath;
    }

    /**
     * Registers a new autoloader definition object which is used to fetch the actual
     * autoload class.
     *
     * @see registerAutoloaders, getRegisteredAutoloaders
     *
     * @param ezcTemplateAutoloaderDefinition $definition
     */
    public function registerAutoloader( ezcTemplateAutoloaderDefinition $definition )
    {
    }

    /**
     * Registers a list of autoloader definition objects which is used to fetch the
     * actual autoload classes.
     *
     * @note Any existing autoloader definitions will be overwritten.
     * @see registerAutoloader, getRegisteredAutoloaders
     *
     * @param array $definitions
     */
    public function setRegisteredAutoloaders( $definitions )
    {
    }

    /**
     * Returns all registered autoloader definition objects as an array. This array
     * can be serialized to a PHP file for quick reinitialisation.
     *
     * @see registerAutoloaders
     *
     * @return array
     */
    public function getRegisteredAutoloaders()
    {
    }

    /**
     * Registers a new resource locator object which is used to fetch source and
     * compiled code.
     *
     * @param string $identifier The identifier for the locator
     * @param ezcTemplateResourceLocator $locator The resource locator handler.
     */
    public function registerResourceLocator( $identifier, ezcTemplateResourceLocator $locator )
    {
    }

    /**
     * Returns the unique configuration instance named $name.
     *
     * @param string $name The name of the instance to fetch.
     * @return ezcTemplateConfiguration
     */
    public static function getInstance( $name = "default" )
    {
        if ( isset( self::$instanceList[$name] ) )
            return self::$instanceList[$name];
        return null;
    }

    /**
     * Sets the unique configuration instance with name $name.
     *
     * @param string $name The name of the instance to set.
     * @param ezcTemplateConfiguration $configuration The configuration option to use
     * as unique instance.
     */
    public static function setInstance( $name = "default", ezcTemplateConfiguration $configuration )
    {
    }

}
?>