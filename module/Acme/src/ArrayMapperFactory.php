<?php
namespace Acme;

use DomainException;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use Laminas\ApiTools\Configuration\ConfigResource;

/**
 * Service factory for the ArrayMapper
 *
 * Requires the Config service in the service locator, and a
 * demolib.array_mapper_path subkey within the configuration that points
 * to a valid filesystem path of a PHP file that will return an array.
 *
 * Passes the data from the file, the path to the file, and a PhpArray config
 * writer to a Laminas\ApiTools\Configuration\ConfigResource instance, and passes the data
 * and the ConfigResource instance to the ArrayMapper.
 */
class ArrayMapperFactory
{
    public function __invoke($services)
    {
        if (! $services->has('config')) {
            throw new DomainException('Cannot create Acme\Utility\ArrayMapper; 
                    missing config dependency');
        }
        
        $config = $services->get('config');
        if (! isset($config['acme']['array_mapper_path'])) {
            throw new DomainException(sprintf(
                'Cannot create %s; missing acme.array_mapper_path configuration',
                ArrayMapper::class
                ));
        }
        
        $path = $config['acme']['array_mapper_path'];
        if (! file_exists($path)) {
            throw new DomainException(sprintf(
                'Cannot create %s; path "%s" does not exist',
                ArrayMapper::class,
                $path
                ));
        }
        
        $data = include $path;
        
        if (! is_array($data)) {
            throw new DomainException(sprintf(
                'Cannot create %s; file "%s" does not return an array',
                ArrayMapper::class,
                $path
                ));
        }
        
        $configResource = new ConfigResource($data, realpath($path), new ConfigWriter());
        return new ArrayMapper($data, $configResource);
    }
}