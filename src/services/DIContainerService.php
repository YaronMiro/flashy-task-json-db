<?php

namespace App\Services;

use DI\ContainerBuilder;
use DI\FactoryInterface;

/**
 * Singleton Wrapper service the DI Mechanism.
 * 
 * It initialize the DI container and exposes the container.
 * THis way it can be easily injected into other service or consumed statically.
 */
final class DIContainerService {
	const DEFINITIONS = __DIR__ . '/../../config-definitions.php';
	protected $container;
	protected static $instance;
	
	/**
	 * __construct
	 *
	 * @return void
	 */
	private function __construct() {
		$builder = new ContainerBuilder();
		$builder->addDefinitions($this::DEFINITIONS);
		$this->container = $builder->build();
	}

	/**
	 * 
	 * The static public instantiation method.
	 * 
	 * @return DIContainerService
	 */
	public static function getInstance(): DIContainerService {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * 
	 * Return the DI container instance.
	 * 
	 * @return FactoryInterface
	 */
	public function getContainer(): FactoryInterface {
		return $this->container;
	}
}

