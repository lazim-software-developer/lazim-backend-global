@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Welcome to Lazim!</h2>

<p>Dear {{ $user->first_name }},</p>

<p>We're excited to welcome you to Lazim! Your account has been successfully created, and you're now ready to manage and oversee property-related operations seamlessly using our platform.</p>

<div class="title">Your Account Details:</div>
<p><strong>Email:</strong> {{ $user->email }}<br>
<strong>Password:</strong> {{ $password }}</p>

<p>
    <a href="{{ env('APP_URL') }}/app/login" class="button">
        Login to Your Account
    </a>
</p>

<div class="title">Key Features of Your Account:</div>
<ul class="feature-list">
    <li>Tenant and Lease Management</li>
    <li>Payment and Billing Management</li>
    <li>Maintenance and Service Requests</li>
    <li>Vendor and Subcontractor Management</li>
    <li>Communication Tools</li>
    <li>Document Management</li>
    <li>Role-Based Access</li>
    <li>Inspection and Compliance</li>
    <li>Community Engagement Features</li>
</ul>

<p>If you have any questions or need assistance while navigating the platform, our support team is here to help.</p>

<p>Thank you for choosing Lazim as your trusted partner. We're committed to helping you streamline your property management tasks and ensure a seamless experience.</p>

<p>
    Regards,<br>
    <strong>-Lazim Team</strong>
</p>
@endsection
