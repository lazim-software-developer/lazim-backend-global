@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
    <td class="paragraph">
        Dear {{$subContractor->name}},
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        We are pleased to inform you that your account has been successfully created by your vendor. Below are the details of your account:
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Account Details:</strong>
    </td>
</tr>
<tr>
    <td class="paragraph" style="padding-left: 20px;">
        ● <strong>Company:</strong> {{$subContractor->company_name}}<br>
        ● <strong>Service Provided:</strong> {{$subContractor->services->pluck('name')->implode(', ')}}<br>
        ● <strong>Start Date:</strong> {{ $start_date }}<br>
        ● <strong>End Date:</strong> {{ $end_date }}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        If you have any questions or require further assistance, please feel free to contact us.
    </td>
</tr>
<tr>
    <td class="paragraph">
        We look forward to working with you.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Regards,
    </td>
</tr>
<tr>
    <td width="100%" height="15"></td>
</tr>
<tr>
    <td>
        <img src="{{url('images/logo.png')}}" alt="Lazim" style="max-width: 100px; height: 50px;">
    </td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
