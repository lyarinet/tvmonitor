<x-filament::page>
    <div class="text-center py-12">
        <h1 class="text-2xl font-bold mb-4">Redirecting to User Guides...</h1>
        <p class="text-gray-500">If you are not redirected automatically, please click the button below.</p>
        <a href="{{ route('guides.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 active:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 disabled:opacity-25 transition">
            Go to User Guides
        </a>
    </div>
    
    <script>
        // Redirect to the guides index page
        window.location.href = "{{ route('guides.index') }}";
    </script>
</x-filament::page> 