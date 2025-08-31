<x-app-layout>
       <x-slot name="header">
           <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
               {{ __('Tambah Klien NAS dari Daftar Router') }}
           </h2>
       </x-slot>
   
       <div class="py-12">
           <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
               <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                   <div class="p-6 text-gray-900 dark:text-gray-100">
                       <form method="POST" action="{{ route('radius.store') }}">
                           @csrf
   
                           <!-- Pilih Router dari Dropdown -->
                           <div>
                               <x-input-label for="router_id" :value="__('Pilih Router')" />
                               <select name="router_id" id="router_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                   <option value="">-- Pilih Router yang Akan Didaftarkan --</option>
                                   @forelse ($routers as $router)
                                       <option value="{{ $router->id }}">{{ $router->name }} ({{ $router->ip_address }})</option>
                                   @empty
                                       <option value="" disabled>Semua router sudah terdaftar.</option>
                                   @endforelse
                               </select>
                               <x-input-error :messages="$errors->get('router_id')" class="mt-2" />
                           </div>
   
                           <!-- Secret -->
                           <div class="mt-4">
                               <x-input-label for="secret" :value="__('Secret (Kata Sandi Bersama)')" />
                               <x-text-input id="secret" class="block mt-1 w-full" type="text" name="secret" required />
                               <p class="mt-1 text-sm text-gray-500">Kata sandi ini harus sama persis dengan yang akan Anda atur di MikroTik.</p>
                               <x-input-error :messages="$errors->get('secret')" class="mt-2" />
                           </div>
   
                           <!-- Description -->
                           <div class="mt-4">
                               <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
                               <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" :value="old('description')" />
                               <x-input-error :messages="$errors->get('description')" class="mt-2" />
                           </div>
   
                           <div class="flex items-center justify-end mt-4">
                               <x-primary-button>
                                   {{ __('Simpan') }}
                               </x-primary-button>
                           </div>
                       </form>
                   </div>
               </div>
           </div>
       </div>
   </x-app-layout>