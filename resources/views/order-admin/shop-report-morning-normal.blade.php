@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Cart')

@push('styles')
<style>
        .div {
            height: 400px;
            /* max-width: 100vw; */
            overflow-x: auto;
            overflow-y: auto;
            position: relative;
            /* margin-top: 100px; */
        }

        table {
            border-collapse: collapse;
            table-layout: fixed;

        }

        thead {
            th {
                position: sticky;
                top: 0;
                left: 0;


                &:first-child {
                    z-index: 3;
                }
            }
        }

        th,
        td {
            /* padding: 10px 100px; */
            text-transform: capitalize;
            border: 1px solid black;
            padding-inline: 20px;
        }

        th {
            background: white;
            /* color: white; */
            white-space: nowrap;

            &:first-child,
            &:nth-child(2)

            /* &:nth-child(3)  */
                {
                position: sticky;
                left: 0px;
                z-index: 3;
                background-color: #feebe9;
                color: #000;
            }
        }

        td {

            &:first-child,
            &:nth-child(2)

            /* &:nth-child(3)  */
                {
                position: sticky;
                left: 0px;
                z-index: 2;
                background-color: #feebe9;
                color: #000;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <!-- ============================================================== -->
                <!-- pageheader  -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Morning Normal Shop Report 
                                <a href="/order-admin/shop-report" class="btn btn-outline-primary">All Routes</a> 
                                <a href="#" class="btn btn-outline-primary">Special Routes</a>
                            </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Morning Normal Shop Report</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end pageheader  -->
                <!-- ============================================================== -->
                <div class="ecommerce-widget">

                    <div class="row">
                        <div class="col-12 col-lg-4 d-grid">
                            <a class="btn btn-outline-primary mb-4" href="/order-admin/shop-report-evening">Evining Normal Report</a>
                        </div>
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-12">
                            <div class="card">
                                <div class="row">
                                    <div class="col-4">
                                        <h5 class="card-header">Today Morning Normal Shop Report &nbsp;&nbsp;
                                            <a href="/order-admin/shop-report-normal-full-screen" type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#morning_shop_wise_report">
                                                Full Screen
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="div">
                                        <table class="table-bordered border-dark">
                                            <thead class="bg-light ">
                                                <tr>
                                                    <th>#</th>
                                                    <th style="min-width: 280px;">Item Code | Item</th>
                                                    @foreach ($header_normal as $head)
                                                    <th style="min-width: 200px;">
                                                        <h6>{{$head->shop_name}}</h6>
                                                        <h6>{{$head->branch_code}}</h6>
                                                    </th>
                                                    @endforeach
                                                    <th class="table-primary"><h6>Normal Route Total</h6></th>
                                                </tr>
                                            </thead>
                                            <tbody style="max-height: 400px;">
                                                @php
                                                $total_normal = 0;
                                                $total_special = 0;
                                                $index = 0;
                                                $timeZone = 'Asia/Colombo';
                                                $currentDate = new DateTime();
                                                @endphp
                                                @foreach ($products as $product)
                                                @php
                                                $total_normal = 0;
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $product->item_number }} | {{ $product->name_english }}</td>
                                                    @foreach ($header_normal as $head)
                                                    @php

                                                    $order = DB::table('orders')
                                                    ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
                                                    ->where('orders.shop', '=', $head->branch_code)
                                                    ->where('carts.item_number', '=', $product->item_number)
                                                    ->where('orders.time_period', '=', 'Morning')
                                                    ->where('orders.status','=','Complete')
                                                    ->whereDate('orders.created_at', $currentDate)
                                                    ->select('carts.*', 'orders.*')
                                                    ->first();

                                                    $order_note = DB::table('orders')
                                                    ->where('shop','=',$head->branch_code)
                                                    ->where('orders.status','=','Complete')
                                                    ->where('orders.time_period', '=', 'Morning')
                                                    ->whereDate('orders.created_at', $currentDate)
                                                    ->first();

                                                    $words = explode(" ", $order_note->note ?? '');
                                                    @endphp

                                                    @if ($order)
                                                    @php
                                                    $total_normal += $order->qty;
                                                    @endphp
                                                    <th class="{{ $order->qty != 0 ? 'table-success' : 'table-danger' }}">
                                                        {{ $order->qty }} | {{ $words[$index] ?? '' }}
                                                    </th>
                                                    @else
                                                    <td>0 | {{ $words[$index] ?? '' }}</td>
                                                    @endif
                                                    @endforeach

                                                    <td class="table-primary">{{ $total_normal }}</td>
                                                </tr>
                                                @php
                                                $index++;
                                                @endphp
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================================== -->
                        <!-- end recent orders  -->

                    </div>

                </div>
            </div>
        </div>
    </div>
    
@endsection

@push('scripts')
<script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this item?');
        }
    </script>
@endpush
