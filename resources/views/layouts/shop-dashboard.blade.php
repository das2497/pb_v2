@extends('layouts.dashboard', ['headerView' => 'shop.components.header', 'menuView' => 'shop.components.menu'])

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/shop-bakery-theme.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/shop-bakery-theme.js') }}?v={{ filemtime(public_path('assets/js/shop-bakery-theme.js')) }}"></script>
@endpush
