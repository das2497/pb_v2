@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Add Rep')

@push('styles')
<style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
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
                            <h2 class="pageheader-title"> Add Rep </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Add Rep</li>
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
                        <!-- ============================================================== -->

                        <!-- ============================================================== -->

                        @if (Auth::user()->role === 'view')
                        <div class="col-12">
                            <div class="card">
                                <h5 class="card-header">All Representatives</h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Name</th>
                                                    <th class="border-0">Email</th>
                                                    <th class="border-0">Contact</th>
                                                    <th class="border-0">Rep Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reps as $rep)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$rep->name}}</td>
                                                    <td>{{$rep->email}}</td>
                                                    <td>{{$rep->contact}}</td>
                                                    <td>{{$rep->type}}</td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="5">{{$reps->links()}}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end recent orders  -->
                        @else
                        <!-- recent orders  -->
                        <!-- ============================================================== -->
                        <div class="col-xl-3 col-lg-12 col-md-6 col-sm-12 col-12">
                            <!-- ============================================================== -->
                            <!-- top perfomimg  -->
                            <!-- ============================================================== -->
                            <div class="card">
                                <h5 class="card-header">Add Representative</h5>
                                <div class="card-body p-0">
                                    <form action="" method="POST" class="p-2">
                                        @csrf
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
                                        <div class="form-group">
                                            <label for="rp_uname" class="col-form-label">Name</label>
                                            <input id="rp_uname" type="text" class="form-control" placeholder="Name" name="name">
                                        </div>
                                        <div class="form-group">
                                            <label for="rp_fname">Email</label>
                                            <input id="rp_fname" type="email" placeholder="Email" class="form-control" name="email">
                                        </div>
                                        <div class="form-group">
                                            <label for="rp_uname" class="col-form-label">Contact</label>
                                            <input id="rp_uname" type="text" class="form-control" placeholder="Contact" name="contact">
                                        </div>
                                        <div class="form-group">
                                            <label for="rp_type">Representative Type</label>
                                            <select class="form-control" id="rp_type" name="type">
                                                <option value="">Select Type</option>
                                                <option value="Outlet">Outlet</option>
                                                <option value="PBD">PBD</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="rp_pass" class="col-form-label">Password</label>
                                            <input id="rp_pass" type="text" class="form-control" placeholder="Password" name="password">
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Add</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- ============================================================== -->
                            <!-- end top perfomimg  -->
                            <!-- ============================================================== -->
                        </div>

                        <div class="col-xl-9 col-lg-12 col-md-6 col-sm-12 col-12">
                            <div class="card">
                                <h5 class="card-header">All Representatives</h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="bg-light">
                                                <tr class="border-0">
                                                    <th class="border-0">#</th>
                                                    <th class="border-0">Name</th>
                                                    <th class="border-0">Email</th>
                                                    <th class="border-0">Contact</th>
                                                    <th class="border-0">Rep Type</th>
                                                    <th class="border-0">Rep Type</th>
                                                    <th class="border-0">Rep Access</th>
                                                    <th class="border-0">Rep Delete</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reps as $rep)
                                                <tr>
                                                    <td>{{$loop->iteration}}</td>
                                                    <td>{{$rep->name}}</td>
                                                    <td>{{$rep->email}}</td>
                                                    <td>{{$rep->contact}}</td>
                                                    <td>{{$rep->type}}</td>
                                                    <form action="/order-admin/rep-update-access" method="POST">
                                                        @csrf

                                                        <td>
                                                            <label class="switch">
                                                                <input type="checkbox" name="access" {{$rep->access == 'on' ? 'checked' : ''}}>
                                                                <span class="slider"></span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" value="{{$rep->email}}" name="rep_email">
                                                            <button type="submit" class="btn btn-outline-primary">Update Access</button>
                                                        </td>
                                                    </form>
                                                    <form action="{{ route('reps.delete',['id'=>$rep->id]) }}" method="POST">
                                                        @csrf
                                                        <td>
                                                            <input type="hidden" value="{{$rep->email}}" name="rep_email">
                                                            <button type="submit" onclick="return confirmDelete();" class="btn btn-outline-danger">Delete</button>
                                                        </td>
                                                    </form>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td></td>
                                                    <td colspan="5">{{$reps->links()}}</td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end recent orders  -->
                        @endif


                    </div>

                </div>
            </div>
        </div>

    </div>
    
@endsection

@push('scripts')
<script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this rep?');
        }
    </script>
@endpush
