@extends('emails.layouts.email')

@section('content')
    <p>Dear {{ $user->first_name }},</p>
    <p>We are thrilled to welcome you to Lazim! Your account has been successfully created.</p>
    <p><strong>Your Account Details:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Password:</strong> {{ $password }} <span style="font-size: 12px; color: #666;">(temporary password, need to change)</span></li>
    </ul>
    <p>Please keep your account credentials secure. You can now log in to your account and start using our platform to access your assigned tasks and services.</p>
    <p>If you have any questions or need assistance, feel free to reach out to us.</p>
    <p>Regards,</p>
    {{-- <img src="{{ url('images/logo.png') }}" alt="Lazim" style="max-width: 80px; height: 30px;"> --}}
    <p>{{ $vendor }}</p>
@endsection
