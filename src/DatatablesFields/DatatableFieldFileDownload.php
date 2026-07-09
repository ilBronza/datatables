<?php

namespace IlBronza\Datatables\DatatablesFields;

class DatatableFieldFileDownload extends DatatableField
{
	public null|int|string $keyPosition = null;
	public null|int|string $valuePosition = null;
	public string $separator = '<br />';
	public string $downloadIcon = 'file-arrow-down';
	public string $downloadTitle = 'Scarica file';
	public $target = '_blank';
	public bool $download = true;
	public ?string $urlMethod = null;
	public ?string $nameMethod = null;
	public ?string $collection = null;

	protected function resolveValue($value)
	{
		$method = $this->function ?? $this->method ?? null;

		if($method && is_object($value) && method_exists($value, $method))
			return $value->{$method}();

		if($this->collection && is_object($value) && method_exists($value, 'getMedia'))
			return $value->getMedia($this->collection);

		return $value;
	}

	public function transformValue($value)
	{
		$value = $this->resolveValue($value);

		if(! $value)
			return [];

		if(is_iterable($value))
			return $this->transformIterableValue($value);

		if($file = $this->transformSingleFile($value))
			return [$file];

		return [];
	}

	protected function transformIterableValue(iterable $value) : array
	{
		$result = [];

		foreach($value as $file)
			if($file = $this->transformSingleFile($file))
				$result[] = $file;

		return $result;
	}

	protected function transformSingleFile($file) : ?array
	{
		$url = $this->getFileUrl($file);
		$name = $this->getFileName($file, $url);

		if(! $url && ! $name)
			return null;

		return [
			'url' => $url,
			'name' => $name ?: $url,
		];
	}

	protected function getFileUrl($file) : ?string
	{
		if(is_string($file))
			return $file;

		if(is_array($file))
			return $file['url']
				?? $file['href']
				?? $file['downloadUrl']
				?? $file['download_url']
				?? $file['fileurl']
				?? $file['file_url']
				?? $file[0]
				?? null;

		if(! is_object($file))
			return null;

		if($this->urlMethod && method_exists($file, $this->urlMethod))
			return $file->{$this->urlMethod}();

		foreach(['getServeImageUrl', 'getUrl', 'getFullUrl'] as $method)
			if(method_exists($file, $method))
				return $file->{$method}();

		return $file->url
			?? $file->href
			?? $file->download_url
			?? $file->file_url
			?? $file->original_url
			?? null;
	}

	protected function getFileName($file, ?string $url = null) : ?string
	{
		if(is_array($file))
			return $file['name']
				?? $file['filename']
				?? $file['file_name']
				?? $file['label']
				?? $file['title']
				?? $file[1]
				?? $this->getNameFromUrl($url);

		if(is_object($file))
		{
			if($this->nameMethod && method_exists($file, $this->nameMethod))
				return $file->{$this->nameMethod}();

			foreach(['getFilename', 'getFileName', 'getName'] as $method)
				if(method_exists($file, $method))
					return $file->{$method}();

			return $file->file_name
				?? $file->name
				?? $file->filename
				?? $this->getNameFromUrl($url);
		}

		return $this->getNameFromUrl($url);
	}

	protected function getNameFromUrl(?string $url) : ?string
	{
		if(! $url)
			return null;

		return basename(parse_url($url, PHP_URL_PATH) ?: $url);
	}

	public function getCustomColumnDefSingleResult()
	{
		$separator = json_encode($this->separator);
		$downloadTitle = json_encode($this->downloadTitle);
		$downloadAttribute = $this->download ? ' download' : '';

		return "
			if(! item || ! Array.isArray(item) || item.length === 0)
			{
				item = '';
			}
			else
			{
				item = item.map(function(file)
				{
					let fileUrl = file.url || '';
					let fileName = file.name || fileUrl;

					if(! fileUrl && ! fileName)
						return '';

					let escapedName = $('<div>').text(fileName).html();

					if(! fileUrl)
						return '<span class=\"ib-datatable-file-download-name\">' + escapedName + '</span>';

					let escapedUrl = $('<div>').text(fileUrl).html().replace(/\"/g, '&quot;');
					let escapedTitle = $('<div>').text(fileName || {$downloadTitle}).html().replace(/\"/g, '&quot;');

					return '<a class=\"ib-datatable-file-download\" href=\"' + escapedUrl + '\" target=\"{$this->target}\" rel=\"noopener\"{$downloadAttribute} title=\"' + escapedTitle + '\"><i class=\"fa-solid fa-{$this->downloadIcon}\"></i> <span class=\"ib-datatable-file-download-name\">' + escapedName + '</span></a>';
				}).filter(Boolean).join({$separator});
			}
		";
	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return "
			if(! item || ! Array.isArray(item))
				return '';

			return item.map(function(file)
			{
				return file.name || file.url || '';
			}).join(' ');
		";
	}

	public function getCustomColumnDefSingleSortResult()
	{
		return $this->getCustomColumnDefSingleSearchResult();
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return $this->getCustomColumnDefSingleSearchResult();
	}
}
