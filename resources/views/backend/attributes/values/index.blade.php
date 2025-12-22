@extends('layouts.backend.app')

@section('content')
<!-- start main container -->
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- start header -->
    <div class="px-4 sm:px-0 mb-4 flex justify-between items-center">
        <h2 class="text-2xl font-bold">
            {{ __('backend.attribute_values.index.title') }}: {{ $attribute->getTranslation('name', app()->getLocale()) }}
        </h2>
        <a href="{{ route('backend.attributes.values.create', $attribute) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            {{ __('backend.attribute_values.actions.create') }}
        </a>
    </div>
    <!-- end header -->

    <!-- start table container -->
    <div class="bg-white shadow rounded-lg">
        <div class="overflow-x-auto">
            <!-- start table -->
            <table class="min-w-full divide-y divide-gray-200">
                <!-- start thead -->
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ strtoupper(app()->getLocale()) }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend.attribute_values.fields.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend.common.actions') }}</th>
                    </tr>
                </thead>
                <!-- end thead -->
                
                <!-- start tbody -->
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($values as $value)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $value->getTranslation('value', app()->getLocale()) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $value->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $value->is_active ? __('backend.common.active') : __('backend.common.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('backend.attributes.values.edit', [$attribute, $value]) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    {{ __('backend.common.edit') }}
                                </a>
                                <form action="{{ route('backend.attributes.values.destroy', [$attribute, $value]) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('backend.common.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        {{ __('backend.common.delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <!-- end tbody -->
            </table>
            <!-- end table -->
        </div>
    </div>
    <!-- end table container -->
</div>
<!-- end main container -->
@endsection
