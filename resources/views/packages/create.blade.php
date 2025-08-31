<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Paket Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('packages.store') }}">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Nama Paket')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="speed" :value="__('Kecepatan (Contoh: 10M/2M)')" />
                            <x-text-input id="speed" class="block mt-1 w-full" type="text" name="speed" :value="old('speed')" required />
                            <x-input-error :messages="$errors->get('speed')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="price" :value="__('Harga (Hanya Angka, Contoh: 150000)')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price')" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>
                        <!-- Pilih Router -->
                        <div>
                            <x-input-label for="router_id" :value="__('Router')" />
                            <select name="router_id" id="router_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Router --</option>
                                @foreach ($routers as $router)
                                    <option value="{{ $router->id }}">{{ $router->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Pilih IP Pool -->
                        <div class="mt-4">
                            <x-input-label for="ip_pool_id" :value="__('IP Pool')" />
                            <select name="ip_pool_id" id="ip_pool_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih IP Pool --</option>
                                @foreach ($ipPools as $ipPool)
                                    <option value="{{ $ipPool->id }}">{{ $ipPool->pool_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan Paket') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>