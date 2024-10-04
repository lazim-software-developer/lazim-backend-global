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
        <strong>Company: </strong> {{$subContractor->company_name}}
    </td>
</tr>

<tr>
    <td class="paragraph">
        <strong>Service Provided: </strong> {{$subContractor->service_provided}}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>Start Date: </strong> {{ \Carbon\Carbon::parse($subContractor->start_date)->format('m-d-Y') }}
    </td>
</tr>
<tr>
    <td class="paragraph">
        <strong>End Date: </strong> {{ \Carbon\Carbon::parse($subContractor->end_date)->format('m-d-Y') }}
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
