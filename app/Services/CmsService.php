<?php

namespace App\Services;

use App\Models\System\CmsContent;

class CmsService
{
    public function get(string $section, string $key, mixed $default = null): mixed
    {
        $record = CmsContent::where('section', $section)->where('key', $key)->first();

        return $record ? $record->value : $default;
    }

    public function set(string $section, string $key, mixed $value, string $type = 'text'): void
    {
        CmsContent::updateOrCreate(
            ['section' => $section, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public function getSection(string $section): array
    {
        return CmsContent::where('section', $section)
            ->pluck('value', 'key')
            ->toArray();
    }

    public function setSection(string $section, array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($section, $key, $value);
        }
    }
}
