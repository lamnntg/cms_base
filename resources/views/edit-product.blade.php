@extends('layouts.admin')

@section('main-content')
<div id="app">
    <h1 class="h3 mb-4 text-gray-800">{{ __('Chỉnh sửa sản phẩm') }}</h1>
    <create-product is-edit :product="{{($product)}}" :categories="{{json_encode($categories)}}"></create-product>
</div>
@endsection

@section('head')
@endsection

@section('scripts')
<script src="{{ mix('js/app.js') }}"></script>
@endsection
