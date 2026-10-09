@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Profile')

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
                            <h2 class="pageheader-title"> Ordering Admin Profile </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Profile</a></li>
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
                                    <h3 class="card-title">Profile Settings</h3>
                                    <form action="{{ route('order-admin.profile-update') }}" method="POST" class="row mb-2">
                                        @csrf
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Update Profile</button>                                      
                                        </div>
                                    </form> 
                                    <hr>
                                    <h3 class="card-title">Change Password</h3>
                                    <form action="{{ route('order-admin.profile-update-password') }}" method="POST" class="row mb-2">
                                        @csrf
                                        <div class="col-12 col-md-6">
                                            <div class="form-group ">
                                                <label for="current_password">Current Password</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>
                                            <div class="form-group ">
                                                <label for="new_password">New Password</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            </div>
                                            <div class="form-group ">
                                                <label for="new_password_confirmation">Confirm New Password</label>
                                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Change Password</button>
                                        </div>
                                    </form>
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
