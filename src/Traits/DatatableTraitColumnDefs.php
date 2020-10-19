<?php

namespace IlBronza\Datatables\Traits;

use IlBronza\Datatables\ColumnDef;
use IlBronza\Datatables\DatatableField;
use Illuminate\Support\Str;

trait DatatableTraitColumnDefs
{
    /**
     * declared here because trait didn't support it
     *
     * @param string $definition
     *
     * @return object \columnDef
     */
    private function newColumnDef($definition)
    {
        return new ColumnDef($definition);
    }

	public function getRowTogglerClasses()
	{
		return "checkIt rowtoggler";
	}

	/**
	 * parse all fields and check if proper columnDef has been set
	 * if not, it sets it
	 */
	public function parseColumnDefs()
	{
		foreach($this->fieldsGroups as $fieldsGroup)
			foreach($fieldsGroup->fields as $field)
				$this->checkFieldColumnDefs($field);
	}

	public function checkIfFieldIsDate(datatableField $field)
	{

		if(($field->view == 'date')||((isset($field->renderAsView))&&($field->renderAsView == 'date')))
		{
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						if(el[" . $field->absoluteIndex . "] !== '')
						{
							let date = moment.unix(el[" . $field->absoluteIndex . "]);

							if(date.isValid())
								return date.format(\"DD/MM/YYYY\");

							return el[" . $field->absoluteIndex . "];
						}

						return '';
					},
				search: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					},
				
				sort: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if(($field->view == 'dateFull')||((isset($field->renderAsView))&&($field->renderAsView == 'dateFull')))
		{
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						if(el[" . $field->absoluteIndex . "] !== '')
						{
							let date = moment.unix(el[" . $field->absoluteIndex . "]);

							if(date.isValid())
								return date.format(\"DD/MM/YYYY H:mm:ss\");

							return el[" . $field->absoluteIndex . "];
						}

						return '';
					},
				search: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					},
				
				sort: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if(($field->view == 'dateReadable')||((isset($field->renderAsView))&&($field->renderAsView == 'dateReadable')))
		{
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						if(el[" . $field->absoluteIndex . "] !== '')
						{
							let date = moment.unix(el[" . $field->absoluteIndex . "]);

							if(date.isValid())
								return date.format(\"DD MMM H:mm\");

							return el[" . $field->absoluteIndex . "];
						}

						return '';
					},
				search: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					},
				
				sort: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if($field->view == 'toggler')
		{
			$this->setShowRowToggler();

			//rowToggler is the new class to check selected rows... checkIt is going to be disabled
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						return '<input data-tableid=\"" . $this->id . "\" name=\"checked[' + el[" . $field->absoluteIndex . "] + ']\" type=\"checkbox\" class=\"" . $this->getRowTogglerClasses() . "\" value=\"' + el[" . $field->absoluteIndex . "] + '\" />';
					},
				search: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					},
				
				sort: function(el)
					{
						return el[" . $field->absoluteIndex . "];
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if($field->view == 'see')
		{
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						return '<a uk-icon=\"link\" href=\"' + el[" . $field->absoluteIndex . "] + '\" ></a>';
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if($field->view == 'links.callto')
		{
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						return '<a class=\"callto\" href=\"tel:' + el[" . $field->absoluteIndex . "] + '\" >' + el[" . $field->absoluteIndex . "] + '</a>';
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if($field->view == 'edit')
		{
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						return '<a uk-icon=\"file-edit\" href=\"' + el[" . $field->absoluteIndex . "] + '\" ></a>';
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if($field->view == 'delete')
		{
			$dateColumnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						return '<button data-trans=\"" . trans('generals.'. $field->modelClass) . "\" data-type=\"" . $field->modelClass . "\" data-id=\"' + el[" . $field->absoluteIndex . "] + '\" uk-tooltip=\"\" title=\"" . trans('links.delete' . $field->modelClass) . "\" class=\"button-delete\" type=\"button\" uk-icon=\"icon: trash\"></button>';
					}
				}
			}";

			$this->customColumnDefs[] = $dateColumnDef;
		}
		else if($field->view == 'belongsToMany')
		{
			$_columnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						let result = '';
						let urlRelation = '" . $field->getRelationModelSprintFShowRoute() . "';
						let urlPivot = '" . $field->getRelationPivotSprintFShowRoute() . "';

						var element = el[" . $field->absoluteIndex . "];

						Object.keys(element).forEach(key => {
							result = result + '<a href=\"' + urlPivot.replace('%s', key) + '\" uk-icon=\"cog\" ratio=\"0.8\"></a> ';
							result = result + '<a href=\"' + urlRelation.replace('%s', element[key].id) + '\">' + element[key].name + '</a><br />';
						});

						return result;
					}
				}
			}";

			$this->customColumnDefs[] = $_columnDef;
		}
		else if($field->view == 'belongsTo')
		{
			$_columnDef = "    {
				targets: [" . $field->absoluteIndex . "],
				data: {
					_: function(el)
					{
						let result = '';
						let urlRelation = '" . $field->getRelationModelSprintFShowRoute() . "';

						var element = el[" . $field->absoluteIndex . "];

						return '<a href=\"' + urlRelation.replace('%s', element.id) + '\">' + element.name + '</a><br />';
					}
				}
			}";

			$this->customColumnDefs[] = $_columnDef;
		}




	}

	/**
	 * check if all field's columnDefs are set in datatable
	 * if not, set them
	 *
	 * @param datatableField $field
	 */
	private function checkFieldColumnDefs(DatatableField $field)
	{
		$fieldColumnDefs = $field->getColumnDefs();

		if(! isset($fieldColumnDefs['className']))
			$fieldColumnDefs['className'] = Str::slug($field->name, '');

		foreach($fieldColumnDefs as $definition => $value)
		{
			$columnDef = $this->getColumnDefByDefinition($definition);
			$columnDef->addIndexToValue($value, $field->absoluteIndex);
		}

		$this->checkIfFieldIsDate($field);

	}

	/**
	 * return table columndef by its name
	 *
	 * @param string $definition
	 *
	 * @return \columnDef
	 */
	private function getColumnDefByDefinition($definition)
	{
		if(! isset($this->columnDefs[$definition]))
			$this->columnDefs[$definition] = $this->newColumnDef($definition);

		return $this->columnDefs[$definition];
	}

}