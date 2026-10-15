<?php

namespace IlBronza\Datatables\DatatablesFields;

use IlBronza\Datatables\Contracts\DatatableFieldContract;
use IlBronza\Datatables\Datatables;

/** Resolves both field families without making either inherit the other. */
class FieldFactory
{
	static function extractOverrideableParametersNameByType(string $fieldType) : array
	{
		$reflection = new \ReflectionClass(static::getClassNameByType($fieldType));
		return array_values(array_filter(array_map(
			static fn ($property) => $property->getName(),
			$reflection->getProperties(\ReflectionProperty::IS_PUBLIC)
		), static fn ($name) => ! in_array($name, ['defaultFilterType', 'name', 'id', 'index', 'table', 'parent', 'fieldsGroup', 'element'], true)));
	}

	static function getPackageClassNameByType(string $fieldType) : string
	{
		$pieces = explode("::", $fieldType);

		$package = array_shift($pieces);

		$fieldType = static::getClassNameByType(implode(".", $pieces), false);

		if ($package == 'app')
			$fullnamespace = "App\\";
		else
			$fullnamespace = get_class(app($package));

		$namespacePieces = explode("\\", $fullnamespace);
		array_pop($namespacePieces);

		return implode("\\", $namespacePieces) . "\Providers\DatatablesFields\\" . $fieldType;
	}

	static function getClassNameByType(string $fieldType, bool $fullQualifiedNamespace = true) : string
	{
		//if contains "::" in string, it's a custom field
		if (strpos($fieldType, "::") !== false)
			return static::getPackageClassNameByType($fieldType);

		$pieces = explode(".", $fieldType);

		$fieldType = ucfirst(array_pop($pieces));

		$folders = implode("", array_map(function ($item)
		{
			if ($item === 'ibDtV2')
				return $item . '\\';

			return ucfirst($item) . '\\';
		}, $pieces));

		if (! $fullQualifiedNamespace)
			return $folders . "DatatableField" . $fieldType;

		return __NAMESPACE__ . '\\' . $folders . "DatatableField" . $fieldType;
	}

	static function createByType(string $fieldName, string $fieldType, array $fieldParameters, int $index = 0, ?DatatableFieldContract $parent = null, ?Datatables $table = null)
	{
		$className = static::getClassNameByType($fieldType);

		return new $className($fieldName, $fieldParameters, $index, $parent, $table);
	}

}
