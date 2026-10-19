<?php

namespace IlBronza\Datatables\Http\Controllers;

use App\Http\Controllers\Controller;
use IlBronza\Datatables\Models\DatatableUserData;
use IlBronza\Datatables\Support\ColumnStyles;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ColumnSettingsController extends Controller
{
    public function update(Request $request, string $tableKey)
    {
        $request->validate([
            'columnIndex' => 'integer|required|min:0',
            'settings_json' => 'string|required|json|max:65536',
            'columnName' => 'string|required|max:128',
        ]);

        $settings = json_decode($request->input('settings_json'), true);
        if (! is_array($settings) || ! isset($settings['selectors']) || ! is_array($settings['selectors']))
            throw ValidationException::withMessages(['settings_json' => 'Invalid column style settings.']);

        $selectors = ColumnStyles::normalize($settings['selectors']);
        DatatableUserData::provideByTableKey($tableKey)->addColumnSettings(
            $request->input('columnName'),
            (int) $request->input('columnIndex'),
            ['selectors' => $selectors]
        );

        return ['success' => true, 'selectors' => (object) $selectors];
    }
}
