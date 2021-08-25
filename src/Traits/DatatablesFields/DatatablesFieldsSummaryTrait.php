<?php

namespace IlBronza\Datatables\Traits\DatatablesFields;

use Spatie\Permission\Models\Role;

trait DatatablesFieldsSummaryTrait
{
    public function getSummaryType()
    {
        return $this->summary;
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
        $value = $this->transformValue($value);

        $this->summaryValues->push($value);

        return $value;
    }

    public function assignSummary(string $summary)
    {
        $this->summary = $summary;
    }


}
