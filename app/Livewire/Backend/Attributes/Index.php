<?php

namespace App\Livewire\Backend\Attributes;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Attribute;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithWireUi;

    public function confirmDeleteAttribute(int $attributeId): void
    {
        $this->confirmDelete(method: 'deleteAttribute', params: $attributeId);
    }

    public function deleteAttribute(int $attributeId): void
    {
        Attribute::query()->findOrFail($attributeId)->delete();

        $this->notifySuccess(__('backend.common.delete_success'));
    }

    public function render()
    {
        $request = request();
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $type = $request->input('type');
        $filterable = $request->input('filterable');
        $required = $request->input('required');

        $query = Attribute::query()
            ->withCount('values');

        if ($search !== '') {
            $locale = app()->getLocale();
            $query->where("name->{$locale}", 'like', '%' . $search . '%');
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if (is_string($type) && array_key_exists($type, Attribute::TYPES)) {
            $query->where('type', $type);
        }

        if ($filterable === '1') {
            $query->where('is_filterable', true);
        } elseif ($filterable === '0') {
            $query->where('is_filterable', false);
        }

        if ($required === '1') {
            $query->where('is_required', true);
        } elseif ($required === '0') {
            $query->where('is_required', false);
        }

        return view('backend.attributes.index', [
            'attributes' => $query->orderBy('id', 'desc')->paginate(20)->withQueryString(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'filterable' => $filterable,
                'required' => $required,
            ],
            'types' => Attribute::TYPES,
            'stats' => [
                'total' => Attribute::count(),
                'active' => Attribute::where('is_active', true)->count(),
                'filterable' => Attribute::where('is_filterable', true)->count(),
                'required' => Attribute::where('is_required', true)->count(),
            ],
        ]);
    }
}

