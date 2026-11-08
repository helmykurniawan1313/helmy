<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Sections;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Support\PortfolioContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function edit(string $section, ?string $locale = null): View|RedirectResponse
    {
        $def = $this->section($section);
        $locale = $this->locale($def, $locale);

        if ($def['translatable'] && $locale === null) {
            return redirect()->route('admin.sections.edit', [$section, 'en']);
        }

        $data = PortfolioContent::for($locale ?? 'en');

        return view('admin.sections.edit', [
            'section' => $def,
            'locale' => $locale,
            'values' => collect($def['fields'] ?? [])->mapWithKeys(fn ($f) => [$f['key'] => data_get($data, $f['key'])])->all(),
            'items' => isset($def['list']) ? array_values(data_get($data, $def['list']['key'], [])) : [],
            'saved' => Content::query()->where('section', $section)->where('locale', $locale ?? 'all')->first(),
        ]);
    }

    public function update(Request $request, string $section, ?string $locale = null): RedirectResponse
    {
        $def = $this->section($section);
        $locale = $this->locale($def, $locale);
        $request->validate($this->rules($def), [], $this->attributes($def));

        $value = [];
        foreach ($def['fields'] ?? [] as $i => $field) {
            $value[$field['key']] = $this->cast($field, $request->input("fields.$i"), $request->file("files.$i"), $section);
        }

        if (isset($def['list'])) {
            $items = [];
            foreach ($request->input('items', []) as $id => $row) {
                $item = [];
                foreach ($def['list']['fields'] as $field) {
                    $item[$field['key']] = $this->cast($field, $row[$field['key']] ?? null, $request->file("items.$id.{$field['key']}_file"), $section);
                }
                $items[] = $item;
            }
            $value[$def['list']['key']] = $items;
        }

        Content::query()->updateOrCreate(
            ['section' => $section, 'locale' => $locale ?? 'all'],
            ['value' => $value],
        );
        PortfolioContent::flush();

        return back()->with('status', $def['title'].' saved'.($locale ? ' ('.strtoupper($locale).')' : '').'.');
    }

    /** Drop the saved version so the section falls back to the file defaults. */
    public function destroy(string $section, ?string $locale = null): RedirectResponse
    {
        $def = $this->section($section);
        $locale = $this->locale($def, $locale);

        Content::query()->where('section', $section)->where('locale', $locale ?? 'all')->delete();
        PortfolioContent::flush();

        return back()->with('status', $def['title'].' reset to the default content.');
    }

    private function section(string $key): array
    {
        return Sections::find($key) ?? abort(404);
    }

    private function locale(array $def, ?string $locale): ?string
    {
        if (! $def['translatable']) {
            return null;
        }

        return $locale === null || in_array($locale, PortfolioContent::LOCALES, true) ? $locale : abort(404);
    }

    private function rules(array $def): array
    {
        $rules = ['items' => ['array', 'max:100']];

        foreach ($def['fields'] ?? [] as $i => $field) {
            $rules["fields.$i"] = $this->fieldRules($field);
            if ($field['type'] === 'image') {
                $rules["files.$i"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
            }
        }

        foreach ($def['list']['fields'] ?? [] as $field) {
            $rules["items.*.{$field['key']}"] = $this->fieldRules($field);
            if ($field['type'] === 'image') {
                $rules["items.*.{$field['key']}_file"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
            }
        }

        return $rules;
    }

    /** Human names for error messages ("Job title is required", not "items.i0.title"). */
    private function attributes(array $def): array
    {
        $names = [];
        foreach ($def['fields'] ?? [] as $i => $field) {
            $names["fields.$i"] = $names["files.$i"] = Str::lower($field['label']);
        }
        foreach ($def['list']['fields'] ?? [] as $field) {
            $names["items.*.{$field['key']}"] = $names["items.*.{$field['key']}_file"] = Str::lower($field['label']);
        }

        return $names;
    }

    private function fieldRules(array $field): array
    {
        $rules = [($field['required'] ?? false) ? 'required' : 'nullable'];

        return [...$rules, ...match ($field['type']) {
            'bool' => ['boolean'],
            'number' => ['integer', 'min:0', 'max:9999'],
            'email' => ['email', 'max:255'],
            'url' => ['url', 'max:500'],
            'select' => ['in:'.implode(',', array_keys($field['options']))],
            'text', 'image' => ['string', 'max:500'],
            default => ['string', 'max:10000'],
        }];
    }

    private function cast(array $field, mixed $input, ?UploadedFile $file, string $section): mixed
    {
        $text = is_string($input) ? trim(str_replace("\r\n", "\n", $input)) : $input;

        return match ($field['type']) {
            'bool' => (bool) $input,
            'number' => $text === null || $text === '' ? null : (int) $text,
            'lines' => array_values(array_filter(array_map('trim', explode("\n", (string) $text)), 'strlen')),
            'paragraphs' => array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', (string) $text)), 'strlen')),
            'tags' => array_values(array_filter(array_map('trim', explode(',', (string) $text)), 'strlen')),
            'image' => $file ? $this->store($file, $section) : ($text ?: null),
            default => $text === '' ? null : $text,
        };
    }

    /** Images go to public/uploads so asset() paths work like the bundled images. */
    private function store(UploadedFile $file, string $section): string
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $name = $name.'-'.Str::lower(Str::random(6)).'.'.$file->extension();
        $file->move(public_path("uploads/{$section}"), $name);

        return "uploads/{$section}/{$name}";
    }
}
