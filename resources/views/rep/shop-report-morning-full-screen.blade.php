@extends('layouts.report-fullscreen')

@section('title', 'Order Admin | Cart')

@push('styles')
<style>
        .div {
            height: calc(100vh - 150px);
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
            color: black;
        }

        th {
            /* background: white; */
            color: black;
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
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 p-2">
                <div class="card p-2">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-3">
                            <h5 class="card-header">Today Morning Shop Report &nbsp;&nbsp;
                            </h5>
                        </div>
                        <div class="col-12 col-lg-7">
                            <form class="row g-2" method="GET" action="/rep/shop-report-full-screen">
                                @csrf
                                <div class="col-12 col-sm-4">
                                    <input type="date" name="date" class="form-control" value="{{ $currentDate }}">
                                </div>
                                <div class="col-12 col-sm-4">
                                    <select name="state" class="form-control">
                                        <option value="Processing" @selected($state === 'Processing')>Processing</option>
                                        <option value="Pending" @selected($state === 'Pending')>Pending</option>
                                        <option value="Complete" @selected($state === 'Complete')>Complete</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <button class="btn btn-primary w-100" type="submit">Search</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-12 col-lg-2">
                            <a href="/rep/shop-report-back-to-report" class="btn btn-outline-dark">Go Back</a>
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
                                        <th style="min-width: 200px;" colspan="2">
                                            <h6>{{$head->shop_name}}</h6>
                                            <h6>{{$head->branch_code}}</h6>
                                        </th>
                                        @endforeach
                                        <th class="table-primary">
                                            <h6>Normal Route Total</h6>
                                        </th>

                                        @foreach ($header_special as $head)
                                        <th style="min-width: 200px;" colspan="2">
                                            <h6>{{$head->shop_name}}</h6>
                                            <h6>{{$head->branch_code}}</h6>
                                        </th>
                                        @endforeach
                                        <th class="table-danger">
                                            <h6>Special Route Total</h6>
                                        </th>

                                        @foreach ($header_pbd as $head)
                                        <th style="min-width: 200px;" colspan="2">
                                            <h6>{{$head->shop_name}}</h6>
                                            <h6>{{$head->branch_code}}</h6>
                                        </th>
                                        @endforeach
                                        <th class="table-danger">
                                            <h6>PBD Route Total</h6>
                                        </th>

                                        <th class="table-warning">
                                            <h6>Total</h6>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    @php
                                    $total_normal = $total_special = $total_pbd = 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $product->item_number }} | {{ $product->name_english }}</td>

                                        {{-- Normal Route --}}
                                        @foreach ($header_normal as $head)
                                        @php
                                        $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                        $orderQty = $order['qty'] ?? 0;
                                        $total_normal += $orderQty;
                                        @endphp
                                        <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                            {{ $orderQty }}
                                        </th>
                                        <th style="max-width: 180px; overflow-x: scroll;">
                                            <span>{{ $order['remark'] ?? '' }}</span>
                                        </th>
                                        @endforeach

                                        <td class="table-primary">{{ $total_normal }}</td>

                                        {{-- Special Route --}}
                                        @foreach ($header_special as $head)
                                        @php
                                        $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                        $orderQty = $order['qty'] ?? 0;
                                        $total_special += $orderQty;
                                        @endphp
                                        <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                            {{ $orderQty }}
                                        </th>
                                        <th style="max-width: 180px; overflow-x: scroll;">
                                            <span>{{ $order['remark'] ?? '' }}</span>
                                        </th>
                                        @endforeach

                                        <td class="table-danger">{{ $total_special }}</td>

                                        {{-- PBD Route --}}
                                        @foreach ($header_pbd as $head)
                                        @php
                                        $order = $orderMap[$head->branch_code][$product->item_number] ?? null;
                                        $orderQty = $order['qty'] ?? 0;
                                        $total_pbd += $orderQty;
                                        @endphp
                                        <th class="{{ $orderQty != 0 ? 'table-success' : '' }}">
                                            {{ $orderQty }}
                                        </th>
                                        <th style="max-width: 180px; overflow-x: scroll;">
                                            <span>{{ $order['remark'] ?? '' }}</span>
                                        </th>
                                        @endforeach

                                        <td class="table-danger">{{ $total_pbd }}</td>

                                        {{-- Total for all routes --}}
                                        <td class="table-warning">{{ $total_normal + $total_special + $total_pbd }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
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
