<?php

namespace IlBronza\Datatables\DatatablesFields\FieldTypesTraits;

trait FieldPlaceholderModelTrait
{
	public function getReplacingString()
	{
		return config("datatables.replace_model_id_string");
	}

	public function getModelClass() : string
	{
		return $this->modelClass;
	}

	public function getFieldPlaceholderModel()
	{
		$model = $this->getModelClass()::make();

		$keyName = $model->getKeyName();

		$model->{$keyName} = $this->getReplacingString();

		return $model;
	}

	protected function replaceUrlScript()
	{
		return "
			let url = '" . $this->getUrl() . "';
			url = url.replace('" . $this->getReplacingString() . "', item[0]);

			if(item[1] === null)
				item[1] = '';

		";
	}


}