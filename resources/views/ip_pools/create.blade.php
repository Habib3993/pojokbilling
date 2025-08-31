<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah IP Pool Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Menampilkan pesan error jika koneksi ke MikroTik gagal --}}
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('ip-pools.store') }}">
                        @csrf
                        
                        <!-- Pilih Router -->
                        <div>
                            <x-input-label for="router_id" :value="__('Router')" />
                            <select name="router_id" id="router_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Router --</option>
                                @foreach ($routers as $router)
                                    <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                        {{ $router->name }} ({{ $router->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('router_id')" class="mt-2" />
                        </div>

                        <!-- Nama Pool -->
                        <div class="mt-4">
                            <x-input-label for="pool_name" :value="__('Nama Pool (Contoh: pool-pppoe)')" />
                            <x-text-input id="pool_name" class="block mt-1 w-full" type="text" name="pool_name" :value="old('pool_name')" required autofocus />
                            <x-input-error :messages="$errors->get('pool_name')" class="mt-2" />
                        </div>

                        <!-- Range IP -->
                        <div class="mt-4">
                            <x-input-label for="ranges" :value="__('Range IP (Contoh: 192.168.100.2-192.168.100.254)')" />
                            <x-text-input id="ranges" class="block mt-1 w-full" type="text" name="ranges" :value="old('ranges')" required />
                            <x-input-error :messages="$errors->get('ranges')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan IP Pool') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
