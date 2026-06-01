<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use IlBronza\Datatables\DatatableFieldsGroup;
use Spatie\Permission\Models\Role;

trait DatatablesFieldsSummaryTrait
{
    public ?array $summaryOperands = null;

    public function getSummaryType()
    {
        return $this->summary;
    }

    public function getSummaryDataAttributes() : array
    {
        if ($this->summary !== 'marginPercent' || empty($this->summaryOperands))
            return [];

        return [
            'summary-revenue-field' => $this->summaryOperands['revenue'],
            'summary-cost-field' => $this->summaryOperands['cost'],
        ];
    }

    public function assignSummaryConfig(array $config, DatatableFieldsGroup $group) : void
    {
        $type = $config['type'] ?? null;

        if (! $type)
            return;

        $this->summary = $type;

        if ($type !== 'marginPercent')
            return;

        $revenueName = $config['revenue'] ?? '';
        $costName = $config['cost'] ?? '';

        if (! $revenueName || ! $costName)
            return;

        if (! $group->getFieldByName($revenueName) || ! $group->getFieldByName($costName))
            return;

        $this->summaryOperands = [
            'revenue' => $revenueName,
            'cost' => $costName,
        ];
    }

    public function getSummaryResult()
    {
        if(! $summaryType = $this->getSummaryType())
            return null;

        if($summaryType == 'average')
            return $this->summaryValues->avg(function ($value)
            {
                return (float) $value;
            });

        if($summaryType == 'distinct')
        {
            return $this->summaryValues->unique(function ($value)
            {
                return strip_tags($value);
            })->implode('-');
        }

        if($summaryType == 'sum')
            return $this->summaryValues->sum(function ($value)
            {
                return (float) $value;
            });

        if(($summaryType == 'sumMinutesArray')||($summaryType == 'sumSecondsArray'))
        {
            $totalMinutes = $this->summaryValues->sum(function ($value)
            {
                $tot = 0;
                foreach($value as $_value)
                    if($_value)
                        $tot += (float) $_value;

                    return $tot;
            });

            $pieces = [];

            if($hours = floor($totalMinutes / 60))
                $pieces[] = $hours . " h";

            if($minutes = $totalMinutes % 60)
                $pieces[] = $minutes . " \'";

            return  implode(" ", $pieces);
        }

        if($summaryType == 'sumMinutes')
        {
            $totalMinutes = $this->summaryValues->sum(function ($value)
            {
                return (float) $value;
            });

            $pieces = [];

            if($hours = floor($totalMinutes / 60))
                $pieces[] = $hours . " h";

            if($minutes = $totalMinutes % 60)
                $pieces[] = $minutes . " \'";

            return  implode(" ", $pieces);

        }


        mori('manca summaryType ' . $summaryType);
    }

    public function transformValueWithSummary($value)
    {
        try
        {
            $value = $this->transformValue($value);            
        }
        catch(\Exception $e)
        {
            return $this->handleError($e);
        }

        $this->summaryValues->push($value);

        return $value;
    }

    public function assignSummary(string $summary) : void
    {
        $this->summary = $summary;
        $this->summaryOperands = null;
    }


}
