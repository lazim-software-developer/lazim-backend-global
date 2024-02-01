</html>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Email Template</title>
	<style>
		body {
			font-family: Arial, sans-serif;
			line-height: 1.6;
			margin: 0;
			padding: 0;
		}

		.container {
			width: 100%;
			max-width: 600px;
			margin: 0 auto;
			overflow: hidden;
			/* Ensure no overflow */
		}

		.top-half {
			background-color: #E6EEF2;
			/* White background */
			padding: 20px;
			/* Adjust padding as needed */
			text-align: center;
		}

		.bottom-half {
			background-color: #085B86;
			/* Light blue background */
			padding: 20px;
			/* Adjust padding as needed */
			text-align: center;
			color: #FFFFFF;
		}

		.header-image {
			width: 80px;
			/* Adjust width as needed */
			display: inline-block;
			position: relative;
			/* Needed to position the pseudo-element */
			top: -20px;
			/* Adjust this value to change where the split occurs */
		}

		.icon-circle {
			border-radius: 50%;
			background-color: #FFFFFF;
			display: inline-block;
			padding: 10px;
			/* Adjust padding as needed to control the size of the circle */
		}

		.header-image::after {
			content: '';
			display: block;
			position: absolute;
			left: 0;
			top: 50%;
			/* This positions the pseudo-element in the middle of the image */
			width: 100%;
			height: 50%;
			background-color: #ADD8E6;
			/* Light blue background for the bottom half of the image */
		}
	</style>
</head>

<body>
	<div class="container">
		<div class="top-half">
			<h3>Dear {{$name}},</h3>
			<br>
			<br>
			<!-- Image appears here with the top half gray -->
			<img src="{{ asset('images/favicon.png') }}" alt="Logo" class="header-image">
		</div>
		<div class="bottom-half">
			<!-- The bottom half of the image will appear to continue here -->
			<h3>Welcome to {{$building}}!</h3>
			<p>We're excited to have you on board. To stay connected and informed, download our community app from here:</p>
			<div style="text-align: center;">
				<a style="display: inline-block; background-color: #FFFFFF; width: 20%;text-decoration: none;" href="https://onelink.to/vb5j7p">Lazim</a>
			</div>
			<!-- <a href="https://apps.apple.com/us/app/lazim/id6475393837" class="icon-circle">
				<img src="{{ asset('images/ios.png') }}" alt="Download on the App Store" style="height: 40px;">
			</a>
			<a href="https://play.google.com/store/apps/details?id=com.punithgoud.lazim" class="icon-circle">
				<img src="{{ asset('images/google.png') }}" alt="Get it on Google Play" style="height: 40px;">
			</a> -->
			<p>Explore features like Services & Facilities, Community, and more!</p>
			<p>Get started today for a seamless community experience.</p>
			<p>Best, Lazim</p>
		</div>
	</div>
</body>

</html>