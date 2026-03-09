<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;

name('dashboard');
middleware(['auth', 'verified',RoleBasedRedirect::class]);


?>
<x-layouts.app>
    @volt
    <div class="w-full bg-gray-100 dark:bg-gray-800 p-6 ">
        <section class="max-w-7xl mx-auto mb-8">
            <div class="flex justify-between items-center mb-4">
                {{-- <h2 class="text-sm  text-gray-900 dark:text-white">Nouveau formulaire</h2> --}}
                <div class="flex space-x-2">

                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- <a href="dashboard/form">
                    <div
                        class="bg-white dark:bg-gray-900 shadow rounded-lg p-4 text-center hover:shadow-lg transition-shadow cursor-pointer">
                        <div
                            class="mx-auto w-16 h-16 bg-gray-200 dark:bg-gray-700 flex items-center justify-center mb-2 rounded">
                            <span class="text-gray-500 dark:text-gray-400 text-2xl">📄</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Nouveau formulaire</p>
                    </div>
                </a> -->
                <div  class="flex flex-col justify-center items-center bg-white border border-gray-200 rounded-lg shadow-sm  md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                    <div class=" items-center p-3">
                        <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg"
                            src="{{ asset('images/home.webp') }}" alt="">
                    </div>
                    <div class="flex flex-col justify-center items-center p-4 leading-normal mx-auto">
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400 text-center ">Here are the biggest enterprise
                            technology acquisitions of 2021 so far, in reverse chronological order.</p>
                        <a href="dashboard/form"
                            class="text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Nouveau
                            formulaire</a>
                    </div>


                </div>

            </div>
        </section>

    </div>

    @endvolt
</x-layouts.app>