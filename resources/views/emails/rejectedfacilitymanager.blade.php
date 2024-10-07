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
        Thank you for your interest in joining Lazim. However, we regret to inform you that your account has been rejected because you have not uploaded the required documents for approval.
    </td>
</tr>
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
        Please upload the appropriate documents in your account to proceed with the approval process.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        To update your documents, click on <a href="https://lazim-vendor-git-feat-property-management-zysktech.vercel.app?_vercel_share=RScYOvn8TEEKtTilktiJ9FE3P7FN4gmS">this link</a>.
    </td>
</tr>
<tr>
    <td width="100%" height="25"></td>
</tr>
<tr>
    <td class="paragraph">
        Once the required documents are submitted, we will review your application again. Thank you for your co-operation.
    </td>
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
