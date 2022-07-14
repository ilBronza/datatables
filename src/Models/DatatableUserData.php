<?php

namespace IlBronza\Datatables\Models;

use Auth;
use Carbon\Carbon;
use IlBronza\CRUD\Traits\Model\CRUDUserOwningsTrait;
use IlBronza\Datatables\Castables\DatatableUserDataCastable;

use Illuminate\Database\Eloquent\Model;

class DatatableUserData extends Model
{
	use CRUDUserOwningsTrait;

	protected $table = 'datatabledata';

	protected $casts = [
		'data' => DatatableUserDataCastable::class
	];

	static $allowedActions = [
		'hideColumn',
		'showColumn'
	];

	public static function boot()
	{
		parent::boot();

		static::retrieved(function ($item)
		{
			$item->touch();
		});

		static::saving(function ($item)
		{
			if(! $item->data)
				$item->data = [];
		});
	}

	static function getAllowedActions()
	{
		return static::$allowedActions;
	}

	public function isTooOld()
	{
		return $this->updated_at->addDays(30) < Carbon::now();
	}

	static function provideBySessionTableKey(string $tableKey)
	{
		return session()->get($tableKey);
	}

	static function retrieveByTableKey(string $tableKey)
	{
		if(! $result = static::byUser()->where('key', $tableKey)->first())
			return null;

		if($result->isTooOld())
			return null;

		return $result;
	}

	static function usesDatabase()
	{
		return ! Auth::guest();
	}

	static function provideByTableKey(string $tableKey)
	{
		if(! static::usesDatabase())
			return static::provideBySessionTableKey($tableKey);

		if($result = static::retrieveByTableKey($tableKey))
			return $result;

		$datatabledata = static::make();

		$datatabledata->user_id = Auth::id();
		$datatabledata->key = $tableKey;
		$datatabledata->save();

		return $datatabledata;
	}
}