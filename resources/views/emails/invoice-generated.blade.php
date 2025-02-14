@extends('emails.layouts.email')

@section('content')
<h2 style="color: #2b6cb0; margin: 0 0 20px 0;">Invoice Notification</h2>

<p>Dear {{ $invoice->resident_name ?? 'Resident' }},</p>

<p>We have generated a new invoice for your account.</p>

<div class="title">Invoice Details:</div>
<p>
    <strong>Invoice Number:</strong> {{ $invoice->invoice_number ?? 'N/A' }}<br>
    <strong>Date of Issue:</strong> {{ $invoice->date ?? 'N/A' }}<br>
    <strong>Due Date:</strong> {{ $invoice->due_date ?? 'N/A' }}<br>
    <strong>Amount Due:</strong> AED {{ ($invoice->rate) ?? 'N/A' }}
</p>

<p>The full invoice is attached to this email as a PDF.</p>

<p>Please ensure the payment is completed by the due date to avoid any late fees or service interruptions.</p>

<p>If you have any questions or require further assistance, feel free to reach out to us.</p>

<p>Thank you for your cooperation.</p>

<p>Regards,</p>

@if(isset($property_manager_logo) && $property_manager_logo)
<p>
    <img src="{{ $property_manager_logo }}" alt="Property Manager" style="max-width: 150px; height: auto;">
</p>
@endif

<p>{{ $pm_oa }}</p>
@endsection
