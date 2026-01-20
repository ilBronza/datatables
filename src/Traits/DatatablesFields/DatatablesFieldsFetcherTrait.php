<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use function config;

trait DatatablesFieldsFetcherTrait
{
	public function getFetcherData(array $parameters = []) : ? array
	{
		if( isset($parameters['fetcher']))
			return $parameters['fetcher'];

		if( isset($this->fetcherData))
			return $this->fetcherData;

		if( isset($this->fetcher))
			return $this->fetcher;

		return null;
	}

	public function setFetcherParameters(array $parameters = [])
	{
//		if (! ($fetcherData = $parameters['fetcher'] ?? false))
//			return;

		if (! $fetcherData = $this->getFetcherData($parameters))
			return;

		if (is_string($fetcherData))
			$fetcherData = [
				'urlMethod' => $fetcherData
			];

		if (! isset($fetcherData['type']))
			$fetcherData['type'] = 'click';

		if (! isset($fetcherData['append']))
			$fetcherData['append'] = true;

		if (! isset($fetcherData['target']))
			$fetcherData['target'] = 'row';

		if (! isset($fetcherData['mode']))
			$fetcherData['mode'] = 'modal';

		$htmlClass = $fetcherData['type'] . 'fetcher' . $fetcherData['mode'];

		$this->addHtmlClass($htmlClass);

		$element = $this->getPlaceholderElement();

		if($element->getKey() == null)
			$element->{$element->getKeyName()} = (config('datatables.replace_model_id_string'));

		$urlMethod = $fetcherData['urlMethod'];
		$url = $element->{$urlMethod}();

		$this->setHeaderDataAttribute('fetch', $url);
//		$this->setHeaderDataAttribute('fetchmode', $fetcherData['mode']);
		$this->setHeaderDataAttribute('fetchtarget', $fetcherData['target']);
	}
}