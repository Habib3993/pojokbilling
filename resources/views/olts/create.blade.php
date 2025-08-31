<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah OLT Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('olts.store') }}">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Nama OLT')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            {{-- REVISI: Tambahkan penampil error --}}
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="ip_address" :value="__('IP Address')" />
                            <x-text-input id="ip_address" class="block mt-1 w-full" type="text" name="ip_address" :value="old('ip_address')" required />
                            {{-- REVISI: Tambahkan penampil error --}}
                            <x-input-error :messages="$errors->get('ip_address')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="username" :value="__('Username Telnet/SSH')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required />
                            {{-- REVISI: Tambahkan penampil error --}}
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password Telnet/SSH')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                            {{-- REVISI: Tambahkan penampil error --}}
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan OLT') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
