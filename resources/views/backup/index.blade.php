<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cadangkan & Pulihkan Data') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Menampilkan pesan sukses/error --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-300 dark:border-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border border-red-300 dark:border-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium">Pencadangan Database</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Pilih metode backup yang sesuai dengan kebutuhan Anda.
                    </p>
                    
                    <!-- Loading indicator -->
                    <div id="loading-indicator" class="hidden mt-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md">
                        <div class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="loading-text" class="text-blue-700 dark:text-blue-200">Memproses backup...</span>
                        </div>
                    </div>
                    
                    {{-- Backup Langsung (Recommended) --}}
                    <div class="mt-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md">
                        <div class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-green-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-green-800 dark:text-green-200">
                                    Backup Langsung (Direkomendasikan)
                                </h4>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                    <p>Backup akan langsung dijalankan tanpa memerlukan queue worker.</p>
                                    <p class="text-xs mt-1">✓ Tidak perlu terminal tambahan ✓ Hasil langsung terlihat ✓ Lebih sederhana</p>
                                </div>
                                <div class="mt-3">
                                    <form id="backup-direct-form" action="{{ route('backup.create') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="use_queue" value="0">
                                        <x-primary-button id="backup-direct-button" type="submit" class="bg-green-600 hover:bg-green-700">
                                            {{ __('Backup Sekarang (Langsung)') }}
                                        </x-primary-button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Backup dengan Queue (Advanced) --}}
                    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md">
                        <div class="flex items-start">
                            <svg class="flex-shrink-0 h-5 w-5 text-yellow-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                    Backup dengan Queue (Advanced)
                                </h4>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p>Backup akan dikirim ke antrian dan memerlukan queue worker.</p>
                                    <p class="text-xs mt-1">⚠️ Perlu terminal tambahan ⚠️ Command: <code>php artisan queue:work</code></p>
                                </div>
                                <div class="mt-3 flex space-x-3">
                                    <form id="backup-queue-form" action="{{ route('backup.create') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="use_queue" value="1">
                                        <button type="submit" id="backup-queue-button" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Backup dengan Queue') }}
                                        </button>
                                    </form>
                                    
                                    <button onclick="checkQueueStatus()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Cek Status Queue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Area untuk menampilkan status queue --}}
                    <div id="queue-status" class="hidden mt-4 p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Status Queue Jobs:</h4>
                        <div id="queue-content" class="text-sm text-gray-600 dark:text-gray-400">
                            Loading...
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium">Riwayat Pencadangan</h3>
                     <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal Dibuat</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ukuran File</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($backups as $backup)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $backup['last_modified'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $backup['file_size'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="{{ route('backup.download', $backup['file_name']) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    Download
                                                </a>
                                                <form action="{{ route('backup.destroy', $backup['file_name']) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus file backup ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            Belum ada file backup.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Handle backup langsung
        document.getElementById('backup-direct-form').addEventListener('submit', function(e) {
            document.getElementById('loading-indicator').classList.remove('hidden');
            document.getElementById('loading-text').textContent = 'Sedang membuat backup... Harap tunggu.';
            
            const button = document.getElementById('backup-direct-button');
            button.disabled = true;
            button.textContent = 'Memproses Backup...';
            button.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // Handle backup queue
        document.getElementById('backup-queue-form').addEventListener('submit', function(e) {
            document.getElementById('loading-indicator').classList.remove('hidden');
            document.getElementById('loading-text').textContent = 'Mengirim ke antrian...';
            
            const button = document.getElementById('backup-queue-button');
            button.disabled = true;
            button.textContent = 'Mengirim ke Queue...';
            button.classList.add('opacity-50', 'cursor-not-allowed');
        });

        function checkQueueStatus() {
            const statusDiv = document.getElementById('queue-status');
            const contentDiv = document.getElementById('queue-content');
            
            statusDiv.classList.remove('hidden');
            contentDiv.innerHTML = 'Loading...';
            
            setTimeout(() => {
                contentDiv.innerHTML = `
                    <div class="space-y-2">
                        <p><strong>Pending Jobs:</strong> <span class="text-yellow-600">Cek dengan command: php artisan queue:monitor</span></p>
                        <p><strong>Failed Jobs:</strong> <span class="text-red-600">Cek dengan command: php artisan queue:failed</span></p>
                        <p><strong>Worker Status:</strong> <span class="text-blue-600">Pastikan worker berjalan dengan: php artisan queue:work</span></p>
                        <div class="mt-3 text-xs text-gray-500">
                            Untuk monitoring real-time, gunakan command di terminal.
                        </div>
                    </div>
                `;
            }, 1000);
        }
    </script>
</x-app-layout>
