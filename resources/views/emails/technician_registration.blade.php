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
            We are thrilled to welcome you to Lazim! Your account has been successfully created.
			</td>
		</tr>
		<tr>
			<td width="100%" height="25"></td>
		</tr>
		<tr>
			<td class="title">
            Your Account Details:
			</td>
		</tr>
		<tr>
			<td width="100%" height="10"></td>
		</tr>
        <tr>
            <td class="paragraph">
                ● <strong>Email: </strong> {{$user->email}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                ● <strong>Password: </strong> {{$password}}
                <div style="font-size: 12px; color: #666;">(temporary password, need to change)</div>
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
            Please keep your account credentials secure. You can now log in to your account and start using our platform to access your assigned tasks and services.
			</td>
		</tr>
        <tr>
            <td class="paragraph">
            If you have any questions or need assistance, feel free to reach out to us.
			</td>
		</tr>
        <tr>
			<td width="100%" height="15"></td>
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
                <img src="{{url('images/logo.png')}}" alt="Lazim" style="max-width: 80px; height: 30px;">
			</td>
		</tr>
	@include('beautymail::templates.minty.contentEnd')

@stop
