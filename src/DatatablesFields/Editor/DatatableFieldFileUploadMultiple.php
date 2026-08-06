<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

use IlBronza\CRUD\Helpers\MediaHelpers\MediaCollectionFinderHelper;

use function config;

class DatatableFieldFileUploadMultiple extends DatatableFieldFileUpload
{
	public $width = '13em';

	public ? string $collection = null;
	public string $counterTitle = 'File caricati';
	public bool $fetchFilesPopup = true;
	public bool $showChecked = true;

	public ? string $fetcherHtmlClass = null;

	public $htmlClasses = [
		'ib-editor-file-upload',
		'ib-editor-file-upload-multiple'
	];

	public function getCollectionName() : string
	{
		return $this->collection ?? $this->name;
	}

	/**
	 * il fetcher non passa da un metodo del model perché serve anche
	 * il nome della collection, che il model non conosce
	 **/
	public function getFetcherData(array $parameters = []) : ? array
	{
		if($fetcherData = parent::getFetcherData($parameters))
			return $fetcherData;

		if(! $this->fetchFilesPopup)
			return null;

		return [
			'url' => MediaCollectionFinderHelper::getCollectionUrl(
				$this->getPlaceholderElement()->getMorphClass(),
				config('datatables.replace_model_id_string'),
				$this->getCollectionName()
			)
		];
	}

	/**
	 * la classe del fetcher non va sull'input file, altrimenti il click
	 * apre la modale invece del selettore di files
	 **/
	protected function addFetcherHtmlClass(string $htmlClass)
	{
		$this->fetcherHtmlClass = $htmlClass;
	}

	protected function getCounterHtmlClassesString() : string
	{
		$classes = ['ib-editor-file-upload-counter', 'uk-margin-small-left'];

		if($this->fetcherHtmlClass)
			$classes[] = $this->fetcherHtmlClass;

		return implode(' ', $classes);
	}

	/**
	 * alla chiusura della modale la riga va rinfrescata, i files
	 * possono essere stati cancellati da lì dentro
	 **/
	protected function getCounterHtmlAttributesString() : string
	{
		if(! $this->fetcherHtmlClass)
			return '';

		return ' data-refreshrowonclose="true"';
	}

	public function getFilesData($element) : array
	{
		$result = [];

		foreach($element->getMedia($this->getCollectionName()) as $media)
			$result[] = [
				'url' => $media->getServeImageUrl(),
				'name' => $media->file_name,
				'checked' => !! $media->getIntegrityCheckedAt()
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

	/**
	 * con showChecked il badge mostra quanti files hanno l'integrity
	 * check popolato sul totale, tipo 3/7
	 **/
	protected function getBadgeContentJs() : string
	{
		if(! $this->showChecked)
			return "files.length";

		return "files.filter(function(file)
				{
					return file.checked;
				}).length + '/' + files.length";
	}

	protected function getFilesCounterJs() : string
	{
		$counterTitle = json_encode($this->counterTitle);
		$counterClasses = $this->getCounterHtmlClassesString();
		$counterAttributes = $this->getCounterHtmlAttributesString();
		$alwaysVisible = $this->fetcherHtmlClass ? 'true' : 'false';
		$badgeContent = $this->getBadgeContentJs();

		return "
			let files = Array.isArray(item[1]) ? item[1] : [];
			let filesCounter = '';

			if(files.length || {$alwaysVisible})
			{
				let filesNames = files.map(function(file)
				{
					return file.name;
				}).join(\"\\n\");

				let filesTitle = $('<div>').text(filesNames || {$counterTitle}).html().replace(/\"/g, '&quot;');

				filesCounter = '<span class=\"{$counterClasses}\"{$counterAttributes} title=\"' + filesTitle + '\"><span class=\"uk-badge\">' + ({$badgeContent}) + '</span></span>';
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
