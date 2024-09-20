<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

trait DatatablesFieldsFetcherTrait
{
	public function setFetcherParameters(array $parameters = [])
	{
		if (! ($fetcherData = $parameters['fetcher'] ?? false))
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
		$urlMethod = $fetcherData['urlMethod'];
		$url = $element->{$urlMethod}();

		$this->setHeaderDataAttribute('fetch', $url);
//		$this->setHeaderDataAttribute('fetchmode', $fetcherData['mode']);
		$this->setHeaderDataAttribute('fetchtarget', $fetcherData['target']);
	}
}