<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySettingController extends Controller
{
    public function edit(Request $request): View
    {
        $company = CompanySetting::get();

        return view('admin.company-settings.edit', compact('company'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'footer' => ['nullable', 'string', 'max:255'],
            'legal_info' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'primary_color' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'commission_threshold' => ['nullable', 'integer', 'min:0'],
            'commission_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'commission_flat' => ['nullable', 'integer', 'min:0'],
        ]);

        $company = CompanySetting::get();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('company', 'public');
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);
        $company->update($validated);

        return back()->with('success', 'Paramètres de l\'entreprise mis à jour.');
    }
}
