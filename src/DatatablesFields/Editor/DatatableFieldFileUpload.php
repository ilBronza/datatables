<?php

namespace IlBronza\Datatables\DatatablesFields\Editor;

class DatatableFieldFileUpload extends DatatableFieldEditor
{
	public $width = '10em';
	public $sortable = false;
	public $filterable = false;
	public ?bool $refreshRow = true;

	public string $buttonLabel = 'Scegli file';
	public string $fileParameter = 'file';
	public ?string $accept = null;
	public $faIcon = 'upload';
	public string $downloadIcon = 'file-arrow-down';
	public string $downloadTitle = 'Scarica file';
	public bool $showFileUploadLabel = false;

	public $htmlClasses = [
		'ib-editor-file-upload'
	];

	public function getFieldSpecificData() : array
	{
		return array_merge(parent::getFieldSpecificData(), [
			'file-field' => $this->fileParameter,
		]);
	}

	public function setParameter($name, $parameter)
	{
		if ($name === 'showLabel')
		{
			$this->showFileUploadLabel = (bool) $parameter;

			return;
		}

		parent::setParameter($name, $parameter);
	}

	protected function getAcceptAttributeString() : string
	{
		if (! $this->accept)
			return '';

		return ' accept="' . e($this->accept) . '"';
	}

	protected function getMediaModelClass() : ?string
	{
		$className = config('media-library.media_model')
			?: 'IlBronza\\CRUD\\Models\\Media';

		if (class_exists($className))
			return $className;

		if (class_exists('IlBronza\\CRUD\\Models\\Media'))
			return 'IlBronza\\CRUD\\Models\\Media';

		return null;
	}

	protected function getFileDataFromValue(mixed $value) : array
	{
		if (! $value)
			return [null, null];

		$value = trim((string) $value);

		if ($mediaClass = $this->getMediaModelClass())
			if ($media = $mediaClass::find($value))
				return [
					method_exists($media, 'getServeImageUrl') ? $media->getServeImageUrl() : $media->getUrl(),
					$media->file_name ?? $media->name,
				];

		if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/'))
			return [
				$value,
				basename(parse_url($value, PHP_URL_PATH) ?: $value),
			];

		return [null, null];
	}

	public function transformValue($value)
	{
		$result = parent::transformValue($value);

		[$fileUrl, $fileName] = $this->getFileDataFromValue($result[1] ?? null);

		$result[] = $fileUrl;
		$result[] = $fileName;

		return $result;
	}

	public function getCustomColumnDefSingleResult()
	{
		$buttonLabel = json_encode($this->buttonLabel);
		$downloadTitle = json_encode($this->downloadTitle);
		$showFileUploadLabel = $this->showFileUploadLabel ? 'true' : 'false';

		if (! $this->userCanEdit())
			return "
				if(item && item[2])
				{
					let fileDownloadUrl = String(item[2]).replace(/\"/g, '&quot;');
					let fileDownloadTitle = $('<div>').text(item[3] || {$downloadTitle}).html();

					item = '<a href=\"' + fileDownloadUrl + '\" download target=\"_blank\" rel=\"noopener\" title=\"' + fileDownloadTitle + '\"><i class=\"fa-solid fa-{$this->downloadIcon}\"></i></a>';
				}
				else
					item = '';
			";

		$classes = $this->getHtmlClassesString();

		return "
			" . $this->substituteUrlParameter() . "

					let fileUploadLabel = (item && item[3]) ? item[3] : {$buttonLabel};
					let fileUploadLabelHtml = $('<div>').text(fileUploadLabel).html();
					let fileUploadLabelText = {$showFileUploadLabel} ? ' <span class=\"ib-editor-file-upload-label\">' + fileUploadLabelHtml + '</span>' : '';
					let fileDownloadLink = '';

				if(item && item[2])
				{
					let fileDownloadUrl = String(item[2]).replace(/\"/g, '&quot;');
					let fileDownloadTitle = $('<div>').text(item[3] || {$downloadTitle}).html();

					fileDownloadLink = '<a class=\"uk-icon-button uk-margin-small-left ib-editor-file-download\" href=\"' + fileDownloadUrl + '\" download target=\"_blank\" rel=\"noopener\" title=\"' + fileDownloadTitle + '\"><i class=\"fa-solid fa-{$this->downloadIcon}\"></i></a>';
				}

						item = '<span uk-form-custom class=\"ib-editor-file-upload-trigger\">" .
							"<input " . $this->getHtmlDataAttributesString() . " type=\"file\" name=\"{$this->fileParameter}\" class=\"{$classes}\"" . $this->getAcceptAttributeString() . " />" .
							"<button type=\"button\" class=\"uk-button uk-button-default uk-button-small\">" . $this->getIconHtml() . "' + fileUploadLabelText + '</button>" .
							"</span>' + fileDownloadLink;
				";
	}
}
