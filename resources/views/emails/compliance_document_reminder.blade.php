@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
    <td class="paragraph">
        Dear {{$complianceDocument->vendor->name}},
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        We are pleased to inform you that your complianceDocument is about to get expired. Below are the details:
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Name: </strong> {{$complianceDocument->doc_name}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Expiry Date: </strong> {{ \Carbon\Carbon::parse($complianceDocument->expiry_date)->format('m-d-Y') }}
    </td>
</tr>
<tr>
    <td class="paragraph">
        Your license will expire in {{ $daysLeft }} days. Please take action before it expires.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        If you have any questions or require further assistance, please feel free to reach out.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Best regards,
    </td>
</tr>
<tr>
    <td width="100%" height="5"></td>
</tr>
<tr>
    <td class="paragraph">
        The Lazim Team
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
