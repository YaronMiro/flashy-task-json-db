<?php

namespace App\Services;
use Ramsey\Uuid\Uuid;

/**
 * Utility Service.
 */
final class UtilsService {
		
	/**
	 * generateUniqueId
	 *
	 * @return void
	 */
	public static function generateUniqueId() {
        return Uuid::uuid4()->toString();
	}

	/**
	 * mergeObjectsRecursive
	 *
	 * @param  mixed $baseObject
	 * @param  mixed $replacementObject
	 * @return void
	 */
	public static function mergeObjectsRecursive($baseObject, $replacementObject) {
		// Convert deeply nested objects to associative arrays.
		$baseObjectAsArray = json_decode(json_encode($baseObject), true);
		$replacementObjectAsArray = json_decode(json_encode($replacementObject), true);

		// Merge Objects Recursively.
		return (object) array_replace_recursive(
			$baseObjectAsArray,
			$replacementObjectAsArray
		);
	}
}