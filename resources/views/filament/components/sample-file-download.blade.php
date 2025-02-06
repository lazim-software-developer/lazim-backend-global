<div class="flex items-center gap-3 p-4">
    <div class="flex-1">
        <p class="text-sm text-gray-600">
            Download the sample file format for import
        </p>
    </div>
    <a 
        href="{{ route('download.sample-building-file') }}" 
        download 
        id="downloadButton"
        class="inline-flex items-center justify-center gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button px-3 py-2 hover:bg-primary-500 focus:bg-primary-500 focus:ring-offset-primary-700 bg-primary-600 text-white shadow focus:ring-white border-transparent"
    >
        <span id="normalState" class="inline-flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Download Sample</span>
        </span>
        
        <span id="loadingState" class="hidden inline-flex items-center gap-1">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Downloading...</span>
        </span>
    </a>
</div>

<script>
document.getElementById('downloadButton').addEventListener('click', function(e) {
    const normalState = document.getElementById('normalState');
    const loadingState = document.getElementById('loadingState');
    
    // Show loading state
    normalState.classList.add('hidden');
    loadingState.classList.remove('hidden');
    
    // Hide loading state after download starts (approximately 1 second)
    setTimeout(() => {
        normalState.classList.remove('hidden');
        loadingState.classList.add('hidden');
    }, 1000);
});
</script>