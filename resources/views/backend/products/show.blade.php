@extends('backend.layouts.app')

@section('title', __('ui.product_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('ui.product_details') }}</h3>
                    <div>
                        <a href="{{ route('backend.products.edit', $product) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> {{ __('ui.edit') }}
                        </a>
                        <a href="{{ route('backend.products.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('ui.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h4>{{ __('ui.basic_information') }}</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">{{ __('ui.name') }}</th>
                                        <td>{{ $product->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.category') }}</th>
                                        <td>{{ $product->category->category_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.seller') }}</th>
                                        <td>{{ $product->seller->company_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.price') }}</th>
                                        <td>{{ $product->price }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.stock') }}</th>
                                        <td>{{ $product->stock }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.status') }}</th>
                                        <td>
                                            @if($product->is_active)
                                                <span class="badge badge-success">{{ __('ui.active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('ui.inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h4>{{ __('ui.product_details') }}</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">{{ __('ui.pack_type') }}</th>
                                        <td>{{ $product->pack_type }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.unit') }}</th>
                                        <td>{{ $product->unit }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.organic') }}</th>
                                        <td>
                                            @if($product->is_organic)
                                                <span class="badge badge-success">{{ __('ui.yes') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('ui.no') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.min_order_price') }}</th>
                                        <td>{{ $product->min_order_price ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ui.min_order_count') }}</th>
                                        <td>{{ $product->min_order_count ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($product->attributeValues->isNotEmpty())
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>{{ __('ui.product_attributes') }}</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('ui.attribute') }}</th>
                                        <th>{{ __('ui.value') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->attributeValues as $attributeValue)
                                    <tr>
                                        <td>{{ $attributeValue->attribute->attribute_name }}</td>
                                        <td>{{ $attributeValue->value }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>{{ __('ui.description') }}</h4>
                            <div class="card">
                                <div class="card-body">
                                    {!! $product->description !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($product->product_image)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>{{ __('ui.product_images') }}</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $product->product_image) }}" class="card-img-top" alt="{{ $product->name }}">
                                    </div>
                                </div>
                                @if($product->product_additional_image)
                                <div class="col-md-4">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $product->product_additional_image) }}" class="card-img-top" alt="{{ $product->name }}">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
