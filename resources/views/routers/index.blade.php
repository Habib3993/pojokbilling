<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Router') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Form Pencarian dan Tombol Tambah --}}
                    <div class="flex justify-between items-center mb-4">
                        <form action="{{ route('routers.index') }}" method="GET" class="w-1/3">
                            <x-text-input type="text" name="search" placeholder="Cari router..." class="w-full" value="{{ request('search') }}" />
                        </form>
                        {{-- 1. Tombol Tambah hanya untuk superadmin --}}
                        @role('superadmin')
                        <a href="{{ route('routers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700">
                            Tambah Router
                        </a>
                        @endrole
                    </div>
                    
                    {{-- Tabel Data Router --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    {{-- 2. Kolom Aksi hanya untuk superadmin --}}
                                    @role('superadmin')
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($routers as $router)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $router->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $router->ip_address }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $router->username }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap" id="status-{{ $router->id }}">
                                            <span class="text-gray-400">Belum dicek</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- 3. Tombol Cek Status hanya untuk superadmin --}}
                                            @role('superadmin')
                                            <button onclick="checkStatus({{ $router->id }})" class="text-blue-600 hover:text-blue-900">
                                                Cek Status
                                            </button>
                                            @endrole
                                            {{-- 4. Seluruh isi Aksi hanya untuk superadmin --}}
                                            @role('superadmin')
                                            <a href="{{ route('routers.edit', $router->id) }}" class="text-indigo-600 hover:text-indigo-900 ml-2">Edit</a>
                                            <form action="{{ route('routers.destroy', $router->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('Yakin hapus router ini?')">Hapus</button>
                                            </form>
                                            @endrole
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- 5. Sesuaikan colspan berdasarkan peran --}}
                                        <td colspan="@role('superadmin') 5 @else 4 @endrole" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Belum ada data router.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Navigasi Halaman --}}
                    <div class="mt-4">
                        {{ $routers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
{{-- 6. Pastikan script hanya ada untuk superadmin --}}
@role('superadmin')
<script>
    function checkStatus(routerId) {
        const statusCell = document.getElementById(`status-${routerId}`);
        statusCell.innerHTML = '<span class="text-yellow-400">Loading...</span>';

        fetch(`/routers/${routerId}/status`)
            .then(response => {
                if (!response.ok) {
                    // Tangani error HTTP seperti 500
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    statusCell.innerHTML = `
                        <span class="text-green-500" title="Uptime">U: ${data.data.uptime}</span> | 
                        <span class="text-green-500" title="CPU Load">C: ${data.data.cpu_load}</span>
                    `;
                } else {
                    // Tampilkan pesan error dari JSON
                    statusCell.innerHTML = `<span class="text-red-500" title="${data.message}">Koneksi Gagal</span>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusCell.innerHTML = `<span class="text-red-500" title="Periksa koneksi atau log server.">Request Error</span>`;
            });
    }
</script>
@endrole
@endpush

</x-app-layout>