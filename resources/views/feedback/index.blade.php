<x-guest-layout>
    <div class="pt-4 bg-gray-100 min-h-screen flex flex-col items-center justify-center">
        <!-- Logo as Title -->
        <div class="mb-12 text-center pt-4">
            <img src="{{ asset('img/logo/symbiosis_logo.png') }}" alt="Lazim Logo" style="height: auto; width: 300px;">
        </div>

       <!-- Emojis Section -->
       <div  style="margin-top: 60px" class="w-full sm:max-w-4xl p-6 bg-white shadow-md overflow-hidden sm:rounded-lg text-center">
        <!-- Main Heading -->
        <h1>We value your feedback!</h1>
        <h1 style="margin-top: 60px"> How's your experience with our building services?</h1>

        <!-- Emojis with interaction -->
        <div class="emojis flex justify-between w-full" style="margin-top: 60px">
            <span class="emoji cursor-pointer transform transition-all duration-300 ease-in-out hover:scale-150 hover:shadow-lg" id="happy" onclick="redirectTo('happy')">
                <img src="{{ asset('img/emojis/smiley-positiv.svg') }}" alt="happy emoji" class="emoji-img">
            </span>
            <span class="emoji cursor-pointer transform transition-all duration-300 ease-in-out hover:scale-150 hover:shadow-lg" id="neutral" onclick="redirectTo('neutral')">
                <img src="{{ asset('img/emojis/smiley-neutral.svg') }}" alt="neutral emoji" class="emoji-img">
            </span>
            <span class="emoji cursor-pointer transform transition-all duration-300 ease-in-out hover:scale-150 hover:shadow-lg" id="sad" onclick="redirectTo('sad')">
                <img src="{{ asset('img/emojis/smiley-negativ.svg') }}" alt="negativ emoji" class="emoji-img">
            </span>
        </div>

        <!-- Hidden form to submit the emoji choice -->
        <form id="emojiForm" action="{{ route('qr.feedback.submit') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="emoji" id="selectedEmoji">
            <input type="hidden" name="buildingName" id="buildingName">
        </form>
    </div>
    </div>

    <script>
        function redirectTo(emoji) {
            // Set the emoji in the hidden input and submit the form
            document.getElementById('selectedEmoji').value = emoji;
            document.getElementById('buildingName').value = "{{ $buildingName }}";
            document.getElementById('emojiForm').submit();
        }
    </script>

    <style>
        /* Background customization */
        body {
            background-color: #f0f4f8;
            margin-top: 90px;
        }

        /* Emojis hover effect */
        .emoji {
            transition: transform 0.3s ease-in-out, background-color 0.3s;
        }

        .emoji:hover {
            transform: scale(1.3);
        }

        /* Emoji container */
        .emojis {
            padding: 20px;
            justify-content: center;
            font-size: 4em;
        }

        /* Mobile-specific styling for emoji size */
        .emoji-img {
            height: 80px;
            width: 80px;
            margin: 10px;
        }

        /* Responsive design for mobile */
        @media (max-width: 600px) {
            .emoji-img {
                height: 80px;
                width: 80px;
                margin: 10px;
            }
        }

        /* Center alignment for content */
        .text-center {
            text-align: center;
        }
    </style>
</x-guest-layout>
