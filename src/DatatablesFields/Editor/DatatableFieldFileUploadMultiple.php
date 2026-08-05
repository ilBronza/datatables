<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldFileUploadMultiple extends DatatableFieldFileUpload
{
	public $width = '13em';

	public ? string $collection = null;
	public string $counterTitle = 'File caricati';

	public $htmlClasses = [
		'ib-editor-file-upload-multiple'
	];

	public function getCollectionName() : string
	{
		return $this->collection ?? $this->name;
	}

	public function getFilesData($element) : array
	{
		$result = [];

		foreach($element->getMedia($this->getCollectionName()) as $media)
			$result[] = [
				'url' => $media->getServeImageUrl(),
				'name' => $media->file_name
			];

		return $result;
	}

	public function transformValue($value)
	{
		$this->element = $value;

		return [
			$value->getKey(),
			$this->getFilesData($value)
		];
	}

	protected function getFilesCounterJs() : string
	{
		$counterTitle = json_encode($this->counterTitle);

		return "
			let files = Array.isArray(item[1]) ? item[1] : [];
			let filesCounter = '';

			if(files.length)
			{
				let filesNames = files.map(function(file)
				{
					return file.name;
				}).join(\"\\n\");

				let filesTitle = $('<div>').text(filesNames || {$counterTitle}).html().replace(/\"/g, '&quot;');

				filesCounter = '<span class=\"ib-editor-file-upload-counter uk-margin-small-left\" title=\"' + filesTitle + '\"><i class=\"fa-solid fa-{$this->downloadIcon}\"></i> <span class=\"uk-badge\">' + files.length + '</span></span>';
			}
		";
	}

	public function getCustomColumnDefSingleResult()
	{
		if(! $this->userCanEdit())
			return $this->getFilesCounterJs() . "
				item = filesCounter;
			";

		$buttonLabel = json_encode($this->buttonLabel);
		$showFileUploadLabel = $this->showFileUploadLabel ? 'true' : 'false';
		$classes = $this->getHtmlClassesString();

		return $this->substituteUrlParameter() . $this->getFilesCounterJs() . "
			let fileUploadLabelHtml = $('<div>').text({$buttonLabel}).html();
			let fileUploadLabelText = {$showFileUploadLabel} ? ' <span class=\"ib-editor-file-upload-label\">' + fileUploadLabelHtml + '</span>' : '';

			item = '<span uk-form-custom class=\"ib-editor-file-upload-trigger\">" .
				"<input " . $this->getHtmlDataAttributesString() . " type=\"file\" multiple name=\"{$this->fileParameter}\" class=\"{$classes}\"" . $this->getAcceptAttributeString() . " />" .
				"<button type=\"button\" class=\"uk-button uk-button-default uk-button-small\">" . $this->getIconHtml() . "' + fileUploadLabelText + '</button>" .
				"</span>' + filesCounter;
		";
	}

	public function getCustomColumnDefSingleSearchResult()
	{
		return "
			if(! Array.isArray(item[1]))
				return '';

			return item[1].map(function(file)
			{
				return file.name;
			}).join(' ');
		";
	}

	public function getCustomColumnDefSingleSortResult()
	{
		return "
			item = Array.isArray(item[1]) ? item[1].length : 0;
		";
	}

	public function getCustomColumnDefSingleResultExport()
	{
		return $this->getCustomColumnDefSingleSearchResult();
	}
}
