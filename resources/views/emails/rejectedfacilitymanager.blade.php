@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
    <td class="paragraph">
        Dear {{$user->first_name}},
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">
        Thank you for your interest in joining Lazim. We regret to inform you that your account application has not been approved at this time.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
@if($remarks)
<tr>
    <td class="paragraph">
        <strong>Reason for Rejection:</strong><br>
        {{$remarks}}
    </td>
</tr>
@endif
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="title">
        Next Steps:
    </td>
</tr>
<tr>
    <td width="100%" height="10"></td>
</tr>
<tr>
    <td class="paragraph">
        Please review the reason for rejection mentioned above and update your application as required.
    </td>
</tr>
<tr>
    <td class="paragraph">
        <ul>
            <li>To update your application, please <a href="https://lazim-vendor-git-feat-property-management-zysktech.vercel.app/login">click here</a></li>
            <li>Once your updates are submitted, we will review your application again promptly</li>
        </ul>
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        We appreciate your understanding and look forward to your revised application. Thank you for your cooperation.
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
<tr>
    <td class="paragraph">
        {{auth()->user()?->first_name}}
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
