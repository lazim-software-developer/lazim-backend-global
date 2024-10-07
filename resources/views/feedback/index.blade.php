<x-guest-layout>
    <div class="pt-4 bg-gray-100 min-h-screen flex flex-col items-center justify-center">
        <!-- Logo as Title -->
        <div class="mb-12 text-center mt-2">
            <img src="{{ asset('img/logo/symbiosis_logo.png') }}" alt="Lazim Logo" class="mx-auto" style="height: auto; width: 100px;">
        </div>

       <!-- Emojis Section -->
       <div class="w-full sm:max-w-4xl p-6 bg-white shadow-md overflow-hidden sm:rounded-lg text-center">
        <!-- Main Heading -->
        <h2 class="text-3xl font-semibold text-gray-800 mb-8">We value your feedback!</h2>
        <h2 class="text-3xl font-semibold text-gray-800 mb-8"> How's your experience with our building services?</h2>

        <!-- Emojis with interaction -->
        <div class="emojis flex justify-between w-full">
            <span class="emoji cursor-pointer transform transition-all duration-300 ease-in-out hover:scale-150 hover:shadow-lg" id="happy" onclick="redirectTo('happy')">😊</span>
            <span class="emoji cursor-pointer transform transition-all duration-300 ease-in-out hover:scale-150 hover:shadow-lg" id="neutral" onclick="redirectTo('neutral')">😐</span>
            <span class="emoji cursor-pointer transform transition-all duration-300 ease-in-out hover:scale-150 hover:shadow-lg" id="sad" onclick="redirectTo('sad')">😢</span>
        </div>

        <!-- Hidden form to submit the emoji choice -->
        <form id="emojiForm" action="{{ route('qr.feedback.submit') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="emoji" id="selectedEmoji">
            <input type="hidden" name="buildingName" id="selectedEmoji">

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
            display: flex;
            justify-content: center;
            gap: 50px;
            font-size: 3em
        }

        /* Center alignment for content */
        .text-center {
            text-align: center;
        }
    </style>
</x-guest-layout>
