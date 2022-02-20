<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Illuminate\Support\Str;

trait DatatablesFieldsFetcherTrait
{
    public function setFetcherParameters(array $parameters = [])
    {
        if(! ($fetcherData = $parameters['fetcher'] ?? false))
            return ;

        if(is_string($fetcherData))
            $fetcherData = [
                'urlMethod' => $fetcherData
            ];

        // mori(get_class_methods($this));

        if(! isset($fetcherData['type']))
            $fetcherData['type'] = 'hover';

        if(! isset($fetcherData['append']))
            $fetcherData['append'] = true;

        if(! isset($fetcherData['target']))
            $fetcherData['target'] = 'row';

        if(! isset($fetcherData['mode']))
            $fetcherData['mode'] = 'tooltip';

        $htmlClass = $fetcherData['type'] . 'fetcher' . $fetcherData['mode'];
        $this->addHtmlClass($htmlClass);

        $element = $this->getPlaceholderElement();
        $urlMethod = $fetcherData['urlMethod'];
        $url = $element->{$urlMethod}();

        $this->setHeaderDataAttribute('fetch', $url);
        $this->setHeaderDataAttribute('fetchtarget', $fetcherData['target']);
    }
}