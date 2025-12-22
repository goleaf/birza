@extends('backend.layouts.app')

@section('title', __('Product Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('Product Details') }}</h3>
                    <div>
                        <a href="{{ route('backend.products.edit', $product) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> {{ __('Edit') }}
                        </a>
                        <a href="{{ route('backend.products.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h4>{{ __('Basic Information') }}</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">{{ __('Name') }}</th>
                                        <td>{{ $product->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Category') }}</th>
                                        <td>{{ $product->category->category_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Seller') }}</th>
                                        <td>{{ $product->seller->company_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Price') }}</th>
                                        <td>{{ $product->price }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Stock') }}</th>
                                        <td>{{ $product->stock }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Status') }}</th>
                                        <td>
                                            @if($product->is_active)
                                                <span class="badge badge-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h4>{{ __('Product Details') }}</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">{{ __('Pack Type') }}</th>
                                        <td>{{ $product->pack_type }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Unit') }}</th>
                                        <td>{{ $product->unit }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Organic') }}</th>
                                        <td>
                                            @if($product->is_organic)
                                                <span class="badge badge-success">{{ __('Yes') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('No') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Min Order Price') }}</th>
                                        <td>{{ $product->min_order_price ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Min Order Count') }}</th>
                                        <td>{{ $product->min_order_count ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($product->attributeValues->isNotEmpty())
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>{{ __('Product Attributes') }}</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('Attribute') }}</th>
                                        <th>{{ __('Value') }}</th>
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
                            <h4>{{ __('Description') }}</h4>
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
                            <h4>{{ __('Product Images') }}</h4>
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
