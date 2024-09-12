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
                We're sorry to inform you that your account application with Lazim has been rejected.
            </td>
        </tr>
        <tr>
            <td width="100%" height="25"></td>
        </tr>
        <tr>
            <td class="title">
                Rejection Details:
            </td>
        </tr>
        <tr>
            <td width="100%" height="10"></td>
        </tr>
        <tr>
            <td class="paragraph">
                <strong>Email: </strong> {{$user->email}}
            </td>
        </tr>
		<tr>
            <td class="paragraph">
                <strong>Remark: </strong> {{$record->remarks}}
            </td>
        </tr>
        <tr>
            <td class="paragraph">
                To proceed with the approval of your account, please upload the required documents again.
            </td>
        </tr>
        <tr>
            <td class="paragraph">
                <a href="{{ env('RESIDENT_DOCUMENT_PAGE') . '/' .encrypt($record->id).'/'.$role}}">Click here to upload your documents</a>
            </td>
        </tr>
        <tr>
            <td class="paragraph">
                If you have any questions or need further assistance, please feel free to contact our support team.
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
