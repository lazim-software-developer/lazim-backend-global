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
		</div>
		<div class="bottom-half">
            <h3>Your {{$complaintType}} Has Been Resolved</h3>
			<p>We are pleased to inform you that your {{$complaintType}} has been successfully addressed and closed.</p>
			<p><strong>Remarks:</strong> {{$remarks}}</p>
			<p>Thank you for your patience and cooperation.</p>
			<p>Best regards,<br>Lazim Team</p>
		</div>
	</div>
</body>

</html>
