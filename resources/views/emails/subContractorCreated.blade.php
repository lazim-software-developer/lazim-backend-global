@extends('emails.layouts.email')

@section('content')
    <p>Dear {{ $subContractor->name }},</p>
    <p>We are pleased to inform you that your account has been successfully created by your vendor.</p>
    <p><strong>Account Details:</strong></p>
    <ul>
        <li><strong>Company:</strong> {{ $vendor_name }}</li>
        <li><strong>Service Provided:</strong> {{ $subContractor->service_description }}</li>
        <li><strong>Start Date:</strong> {{ $start_date }}</li>
        <li><strong>End Date:</strong> {{ $end_date }}</li>
    </ul>
    <p>If you have any questions or require further assistance, please feel free to contact us.</p>
    <p>We look forward to working with you.</p>
    <p>Regards,</p>
    {{-- <img src="{{ url('images/logo.png') }}" alt="Lazim" style="max-width: 80px; height: 30px;"> --}}
    <p>{{ $vendor_name }}</p>
@endsection
