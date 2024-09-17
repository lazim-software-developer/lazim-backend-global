@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
    <td class="paragraph">
        Hello {{$user->name}},
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td>
        Your account has been deactivated by the system administrator.
    </td>
</tr>
<tr>
    <td width="100%" height="20"></td>
</tr>
<tr>
    <td class="paragraph">If you have any questions or need assistance,
         our support team is just an email away at info@lazim.ae</td>
</tr>

<tr>
    <td width="100%" height="25"></td>
</tr>

<tr>
    <td class="paragraph">
        Warm regards,
    </td>
</tr>
<tr>
    <td width="100%" height="5"></td>
</tr>
<tr>
    <td class="paragraph">
        Lazim team
    </td>
</tr>

<tr>
    <td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop
