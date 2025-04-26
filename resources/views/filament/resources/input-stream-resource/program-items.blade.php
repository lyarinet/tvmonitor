@if (empty($programList))
    <div class="text-gray-500">
        No programs found yet. Use the "Scan Programs" button in the Options tab.
    </div>
@else
    <div class="space-y-4">
        @foreach ($programList as $program)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="font-medium">{{ $program['description'] }}</div>
                <button 
                    type="button" 
                    class="px-3 py-1 text-sm text-white bg-emerald-500 hover:bg-emerald-600 rounded-md flex items-center gap-1"
                    x-data
                    x-on:click="
                        $el.closest('form').querySelector('[name=program_id]').value = '{{ $program['id'] }}';
                        $el.closest('form').querySelector('[name=program_id]').dispatchEvent(new Event('input'));
                        $dispatch('notify', {
                            message: 'Program ID {{ $program['id'] }} has been set',
                            icon: 'heroicon-o-check-circle',
                            iconColor: 'success',
                            timeout: 3000
                        })
                    "
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Use This Program
                </button>
            </div>
        @endforeach
    </div>
@endif 