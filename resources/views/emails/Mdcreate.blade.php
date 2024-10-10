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
            We're excited to have you on board with Lazim! Your account has been successfully created, and we're thrilled to welcome you to our community.
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
                <strong>Email: </strong> {{$user->email}}
			</td>
		</tr>
        <tr>
            <td class="paragraph">
                <strong>Password: </strong> {{$password}}
                <!-- (We recommend changing this password upon your first login for security reasons.) -->
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
		<tr>
            <td class="paragraph">
                To access your account and use our platform, click on
                @if($user->role->name === 'facility_manager')
               <a href="https://qa-admin.lazim.ae/">this link</a>
                @else
               <a href="https://lazim-vendor-git-feat-property-management-zysktech.vercel.app?_vercel_share=KgoZDmnZy6lgnAUnF6IcdnQZRekmgUez">this link</a>
                @endif
			</td>
		</tr>
        <tr>
			<td width="100%" height="25"></td>
		</tr>
        <tr>
            <td class="paragraph">
            Thank you for choosing Lazim. We're confident that you'll find great value in our platform, and we look forward to serving you.
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
