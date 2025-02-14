@extends('emails.layouts.email')

@section('content')
    <p>Dear {{ $user->first_name }},</p>
    <p>We are delighted to inform you that your account has been successfully approved by the Property Management team.</p>
    <p>Welcome to Lazim!</p>
    <p><strong>Account Details:</strong></p>
    <ul>
        <li><strong>Account Name:</strong> {{ $user->first_name }}</li>
        <li><strong>Email Address:</strong> {{ $user->email }}</li>
    </ul>
    <p>You can access our platform to manage tasks, monitor property requests, and streamline your operations efficiently.</p>
    <p>If you have any questions or require assistance to get started, our support team is available at 043206789.</p>
    <p>Thank you for partnering with Lazim. We are excited to work together in delivering exceptional property management services.</p>
    <p>Regards,</p>
    @if($user->ownerAssociation && $user->ownerAssociation->first() && $user->ownerAssociation->first()->profile_photo)
        <img src="{{ env('AWS_URL') . '/' . $user->ownerAssociation->first()->profile_photo }}" alt="Owner Association Logo" style="max-width: 80px; height: 30px;">
    @endif
    <p>{{ $pm_oa }}</p>
@endsection
