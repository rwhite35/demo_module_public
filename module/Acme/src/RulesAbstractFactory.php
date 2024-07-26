<?php
namespace Acme\Utilities;
/**
 * @author Ron White, ronwhite562@gmail.com
 * @version 1.0, [Dev-Master]
 * @since 2018-04-19
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * 
 * Contract for writing post input data to target JSON strings. The result
 * is well formed JSON string for each notification rule.
 * 
 * Note: PHP ^7.0 uses Type return declaration (:) which was added in PHP 7
 */

abstract class RulesAbstractFactory
{
    abstract public function convertPostToString(array $post) : Text;
}

/**
 * @abstract Text
 * JsonFactory passes string which returns an object to the calling client 
 * @return object $this, proto object(Acme\Utilities\JsonFactory)
 * * public 'string' => string '{"route_guide_rule":[ {"client_id":"9"},..,{"active":"enabled"}]}'
 */
abstract class Text
{
    /**
     * String Object
     */
    public $text;
    
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    
}
?>