<?php
/**
 * Helper trait allowing access to private and protected methods and properties.
 *
 * @package PrivateAccess
 */

use ReflectionClass;

/**
 * Trait PrivateAccess
 *
 * @package Fixtures
 */
trait PrivateAccess {

	/**
	 * Get a private or protected property from an object.
	 *
	 * @param object $class_object Object to get property from.
	 * @param string $property     Property to get.
	 *
	 * @return mixed
	 */
	public function get_private_property( $class_object, $property ) {

		$reflection = new ReflectionClass( $class_object );

		$property = $reflection->getProperty( $property );
		$property->setAccessible( true );

		return $property->getValue( $class_object );
	}

	/**
	 * Set a private or protected property on an object.
	 *
	 * @param object $class_object Object to set property on.
	 * @param string $property     Property to set.
	 * @param mixed  $value        Value to set.
	 *
	 * @return void
	 */
	public function set_private_property( $class_object, $property, $value ) {

		$reflection = new ReflectionClass( $class_object );

		$property = $reflection->getProperty( $property );
		$property->setAccessible( true );
		$property->setValue( $class_object, $value );
	}

	/**
	 * Call a private or protected method on an object.
	 *
	 * @param object $class_object Object to call method on.
	 * @param string $method       Method to call.
	 * @param array  $args         Arguments to pass to method.
	 *
	 * @return mixed
	 */
	public function call_private_method( $class_object, $method, array $args = array() ) {

		$reflection = new ReflectionClass( $class_object );

		$method = $reflection->getMethod( $method );
		$method->setAccessible( true );

		return $method->invokeArgs( $class_object, $args );
	}
}
