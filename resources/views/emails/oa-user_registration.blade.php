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
		Welcome to Lazim, and thank you for joining our community!
		<!-- We're excited to have you on board with Lazim! Your account has been successfully created, and we're thrilled to welcome you to our community. -->
	</td>
</tr>
<tr>
	<td>We're excited to have you on board. To get started, fill all the required information related to your Owner association.</td>
</tr>
<tr>
	<td>If you have any questions or need assistance, our support team is just an email away at info@lazim.ae</td>
</tr>
<tr>
	<td>Remember, you can always manage your email preferences by visiting your account settings.</td>
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
	<td class="title">
		Get Started:
	</td>
</tr>

<tr>
	<td width="100%" height="10"></td>
</tr>

<tr>
	<td class="paragraph">
		To access your account and use our platform, click on <a href="https://qa-admin.lazim.ae/">this link</a>
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