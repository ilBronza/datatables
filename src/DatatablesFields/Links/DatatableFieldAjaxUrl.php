<?php

namespace IlBronza\Datatables\DatatablesFields\Links;

/**
 * An AJAX action link which preserves the URL/text resolution provided by
 * DatatableFieldLink.
 *
 * After a successful request the containing DataTables row is reloaded, even
 * when the endpoint returns no explicit `action: refreshRow` instruction.
 */
class DatatableFieldAjaxUrl extends DatatableFieldAjax
{
    public bool $refreshRowOnSuccess = true;

    public $dataAttributes = [
        'type' => 'GET'
    ];    

    public function getFieldSpecificData() : array
    {
        return array_merge(
            parent::getFieldSpecificData(),
            [
                'refresh-row-on-success' => $this->refreshRowOnSuccess ? 'true' : 'false',
            ]
        );
    }
}
