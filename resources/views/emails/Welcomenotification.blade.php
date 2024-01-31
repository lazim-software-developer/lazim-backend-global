@extends('beautymail::templates.minty')

@section('content')

@include('beautymail::templates.minty.contentStart')
<tr>
	<td class="paragraph">
		Dear {{$name}},
	</td>
</tr>
<tr>
	<td width="100%" height="20"></td>
</tr>
<tr>
	<td class="paragraph">
		Welcome to Lazim! We're excited to have you on board. To stay connected and informed, download our community app:
	</td>
</tr>
<tr>
	<td width="100%" height="25"></td>
</tr>
<tr>
	<td align="center">
		<img src="{{ asset('images/favicon.png') }}" alt="" style="max-width: 100%; height: auto;">
	</td>
</tr>

<tr>
	<td width="100%" height="10"></td>
</tr>

<tr>
	<td class="paragraph" style="text-align: center;">
		<p>Download the Lazim app from store:</p>
	</td>
</tr>
<tr>
	<td align="center" class="paragraph">
		<table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
			<tr>
				<td align="right" style="padding-right: 20px;">
					<a href="https://apps.apple.com/us/app/lazim/id6475393837">
						<img src="{{ asset('images/ios.png') }}" alt="Download on the App Store" style="height: 40px; vertical-align: middle; display: inline-block;">
					</a>
				</td>
				<td align="left">
					<a href="https://play.google.com/store/apps/details?id=com.punithgoud.lazim">
						<img src="{{ asset('images/google.png') }}" alt="Get it on Google Play" style="height: 40px; vertical-align: middle; display: inline-block;">
					</a>
				</td>
			</tr>
		</table>
	</td>
</tr>

<tr>
	<td width="100%" height="25"></td>
</tr>

<tr>
	<td class="paragraph">
		Explore features like Services & Facilities, Community, and more!
	</td>
</tr>
<tr>
	<td width="100%" height="25"></td>
</tr>

<tr>
	<td class="paragraph">
		Get started today for a seamless community experience.
	</td>
</tr>
<tr>
	<td width="100%" height="5"></td>
</tr>
<tr>
	<td class="paragraph">
		Best,
		Lazim
	</td>
</tr>

<tr>
	<td width="100%" height="25"></td>
</tr>
@include('beautymail::templates.minty.contentEnd')

@stop