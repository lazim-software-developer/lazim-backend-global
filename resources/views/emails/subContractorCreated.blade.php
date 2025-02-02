@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Account Creation Confirmation</h2>

<p>Dear {{ $subContractor->name }},</p>

<p>We are pleased to inform you that your account has been successfully created by your vendor.</p>

<div class="title">Account Details:</div>
<p>
    <strong>Company:</strong> {{ $subContractor->company_name }}<br>
    <strong>Service Provided:</strong> {{ $subContractor->services->pluck('name')->implode(', ') }}<br>
    <strong>Start Date:</strong> {{ $start_date }}<br>
    <strong>End Date:</strong> {{ $end_date }}
</p>

<p>If you have any questions or require further assistance, please feel free to contact us.</p>

<p>We look forward to working with you.</p>

<p>Regards,</p>

<p>{{ $subContractor->vendor->name ?? 'Lazim Team' }}</p>
@endsection
