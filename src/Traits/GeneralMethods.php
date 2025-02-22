<?php

declare(strict_types=1);

namespace Zk\DataGrid\Traits;

/**
 * General methods
 */
trait GeneralMethods
{
	/**
	 * Convert array to string attributes.
	 * 
	 * @param array $attributes
	 * @param array $exclude // The attribute to exclude from the output.
	 * @return string
	 */
	public function printAttributes(array $attributes = [], $exclude = []): string
	{
		$htmlAttributes = [];
		foreach ($attributes as $key => $value) {
			if (in_array($key, $exclude)) continue;
			$htmlAttributes[] = (is_numeric($key)) ? trim((string) $value) : $key . '="' . trim((string) $value) . '"';
		}
		return implode(' ', $htmlAttributes);
	}

}
