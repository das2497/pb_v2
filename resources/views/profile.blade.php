<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEOPLES BAKERS | Profile</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f5f9;
            padding: 40px 0;
        }

        .profile-card {
            max-width: 600px;
            margin: 0 auto 24px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="profile-card">
            <a href="{{ url()->previous() }}" class="text-decoration-none">&larr; Back</a>
        </div>

        <div class="card profile-card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Profile Information</h5>
                <p class="text-muted small">Update your account's profile information and email address.</p>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                            <p class="small mt-2">
                                Your email address is unverified.
                                <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">Click
                                    here to re-send the verification email.</button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="text-success small mt-2">A new verification link has been sent to your
                                    email address.</p>
                            @endif
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                    @if (session('status') === 'profile-updated')
                        <span class="text-success small ms-2">Saved.</span>
                    @endif
                </form>
            </div>
        </div>

        <div class="card profile-card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Update Password</h5>
                <p class="text-muted small">Ensure your account is using a long, random password to stay secure.</p>

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="update_password_current_password" class="form-label">Current Password</label>
                        <input type="password" id="update_password_current_password" name="current_password"
                            class="form-control" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password" class="form-label">New Password</label>
                        <input type="password" id="update_password_password" name="password" class="form-control"
                            autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" id="update_password_password_confirmation" name="password_confirmation"
                            class="form-control" autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                    @if (session('status') === 'password-updated')
                        <span class="text-success small ms-2">Saved.</span>
                    @endif
                </form>
            </div>
        </div>

        <div class="card profile-card shadow-sm border-danger-subtle">
            <div class="card-body">
                <h5 class="card-title">Delete Account</h5>
                <p class="text-muted small">Once your account is deleted, all of its resources and data will be
                    permanently deleted. Before deleting your account, please download any data or information
                    that you wish to retain.</p>

                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                    data-bs-target="#confirmUserDeletion">Delete Account</button>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $errors->userDeletion->isNotEmpty() ? 'show' : '' }}" id="confirmUserDeletion"
        tabindex="-1" style="{{ $errors->userDeletion->isNotEmpty() ? 'display:block;' : '' }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <h5>Are you sure you want to delete your account?</h5>
                        <p class="text-muted small">Once your account is deleted, all of its resources and data
                            will be permanently deleted. Please enter your password to confirm you would like to
                            permanently delete your account.</p>

                        <input type="password" name="password" class="form-control" placeholder="Password">
                        @error('password', 'userDeletion')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if ($errors->userDeletion->isNotEmpty())
        <div class="modal-backdrop fade show"></div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
