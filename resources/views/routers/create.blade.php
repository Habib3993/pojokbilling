<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Router Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('routers.store') }}">
                        @csrf
                        
                        <!-- Nama Router -->
                        <div>
                            <x-input-label for="name" :value="__('Nama Router (Contoh: Router Pusat)')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- IP Address -->
                        <div class="mt-4">
                            <x-input-label for="ip_address" :value="__('IP Address')" />
                            <x-text-input id="ip_address" class="block mt-1 w-full" type="text" name="ip_address" :value="old('ip_address')" required />
                            <x-input-error :messages="$errors->get('ip_address')" class="mt-2" />
                        </div>

                        <!-- Port API MikroTik -->
                        <div class="mt-4">
                            <x-input-label for="port" :value="__('Port API MikroTik')" />
                            <x-text-input id="port" class="block mt-1 w-full" 
                                        type="number" name="port" 
                                        :value="old('port', 8728)" required />
                            <x-input-error :messages="$errors->get('port')" class="mt-2" />
                        </div>

                        <!-- Username API -->
                        <div class="mt-4">
                            <x-input-label for="username" :value="__('Username API MikroTik')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <!-- Password API -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password API MikroTik')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan Router') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
