@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Logs')

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
                            <h2 class="pageheader-title"> Ordering Admin Logs </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Logs</a></li>
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

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form action="" method="GET" class="row mb-2">
                                        <div class="col-2">
                                            <input type="date" class="form-control" name="date" id="">
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control" placeholder="Search..." name="search" id="">
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-primary" id="search">Search</button>
                                        </div>
                                    </form>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="border-0">DateTime</th>
                                                            <th class="border-0">Type</th>
                                                            <th class="border-0">User</th>
                                                            <th class="border-0">Messege</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($logs as $log)
                                                        <tr>
                                                            <td>[{{$log->created_at}}]</td>
                                                            <td>{{$log->type}}</td>
                                                            <td>{{$log->user}}</td>
                                                            <td>{{$log->message}}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        {{$logs->links()}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- end footer -->
            <!-- ============================================================== -->
        </div>

        
@endsection

@push('scripts')
<script>
            document.getElementById('refresh').addEventListener('click', function() {
                // Disable the button
                this.disabled = true;

                // You can also perform other actions here
                // For example, refreshing the content or triggering an API call
            });
        </script>
@endpush
