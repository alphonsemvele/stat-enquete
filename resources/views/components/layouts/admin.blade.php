<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Page Title' }}</title>
    <script src="https://unpkg.com/@heroicons/vue@2.0.18/dist/heroicons.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    @livewireStyles
    <style>
        html,
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            /* Remplace height: 100% */
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .sidebar nav ul {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .sidebar nav .logout-section {
            margin-top: auto;
        }

        /* Animation pour la page active */
        .active {
            background-color: #374151;
            /* bg-gray-700 comme couleur de fond active */
            animation: pulse 2s infinite ease-in-out;
        }

        @keyframes pulse {

            0%,
            100% {
                background-color: #374151;
                /* bg-gray-700 */
            }

            50% {
                background-color: #4b5563;
                /* Une teinte légèrement plus claire pour l'effet */
            }
        }
    </style>
</head>

<body>
    <div class="min-h-screen bg-gray-50">
        <main>
            <div class="flex min-h-screen">
                <!-- Sidebar -->
                <div
                    class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 dark:bg-gray-950 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col sidebar">
                    <div class="flex items-center justify-between p-4 border-b border-gray-700 flex-shrink-0">
                        <div class="flex items-center space-x-2">
                            <!-- <img src="{{ asset('logocamtel.png') }}" class="h-16" alt="Flowbite Logo" /> -->
                            <h2 class="text-xl font-bold">STAT ENQUETE </h2>
                        </div>
                        <button class="lg:hidden text-white hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <nav class="p-4 flex-1 flex flex-col">
                        <ul class="space-y-2 flex-1">
                            <li>
                                <a href="/admin"
                                    class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group {{ request()->is('admin') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                        </path>
                                    </svg>
                                    <span class="font-medium">Home</span>
                                </a>
                            </li>
                            <li>
                                <a href="/admin/type"
                                    class="flex items-center p-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 group {{ request()->is('admin/type') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14-4H3m16 8H7m12 4H9"></path>
                                    </svg>
                                    <span class="font-medium text-white">Types</span>
                                </a>
                            </li>
                            <li>
                                <a href="/admin/property"
                                    class="flex items-center p-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 group {{ request()->is('admin/property') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7m-2 2v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7"></path>
                                    </svg>
                                    <span class="font-medium text-white">Property</span>
                                </a>
                            </li>

                            <li>
                                <a href="/admin/icone"
                                    class="flex items-center p-3 rounded-lg hover:bg-blue-700 transition-colors duration-200 group {{ request()->is('admin/icone') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.5 3H12H8C6.9 3 6 3.9 6 5v4.5m7.5-6l6 6m0 0l-6 6m6-6H9"></path>
                                    </svg>
                                    <span class="font-medium text-white">Icones</span>
                                </a>
                            </li>
                            <!-- <li>
                                <a href="/admin/users"
                                    class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group {{ request()->is('admin/users') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                        </path>
                                    </svg>
                                    <span class="font-medium">Users</span>
                                </a>
                            </li> -->
                            <li>
                                <a href="/admin/forms"
                                    class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group {{ request()->is('admin/forms') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <span class="font-medium">Form</span>
                                </a>
                            </li>
                            <li>
                                <a href="/admin/stats"
                                    class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group {{ request()->is('admin/stats') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                    <span class="font-medium">Statistiques</span>
                                </a>
                            </li>
                            <li>
                                <a href="/admin/settings"
                                    class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group {{ request()->is('admin/settings') ? 'active' : '' }}">
                                    <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="font-medium">Paramètres</span>
                                </a>
                            </li>
                        </ul>
                        <div class="logout-section mt-8 pt-4 border-t border-gray-700">
                            <a href="/logout"
                                class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200 group {{ request()->is('logout') ? 'active' : '' }}">
                                <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                <span class="font-medium">Déconnexion</span>
                            </a>
                        </div>
                    </nav>
                </div>
                <!-- Contenu principal -->
                <div class="w-full min-h-screen">
                    <nav class="bg-white border-gray-200 dark:bg-gray-900 dark:border-gray-700 shadow-md">
                        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
                            <button type="button" data-collapse-toggle="navbar-search" aria-controls="navbar-search"
                                aria-expanded="false"
                                class="md:hidden text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5 me-1">
                                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                                <span class="sr-only">Search</span>
                            </button>
                            <div class="relative hidden md:block">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                    <span class="sr-only">Search icon</span>
                                </div>
                                <input type="text" id="search-navbar"
                                    class="block w-full p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Search...">
                            </div>
                            <button data-collapse-toggle="navbar-dropdown" type="button"
                                class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                                aria-controls="navbar-dropdown" aria-expanded="false">
                                <span class="sr-only">Open main menu</span>
                                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 17 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
                                </svg>
                            </button>
                            <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
                                <button type="button"
                                    class="flex text-sm bg-gray-800 rounded-full md:me-0 focus:ring-4 focus:ring-gray-300"
                                    id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown"
                                    data-dropdown-placement="bottom">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="w-8 h-8 rounded-full" src="{{ asset('images/user.png') }}"
                                        alt="user photo">
                                </button>
                                <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow-sm"
                                    id="user-dropdown">
                                    <div class="px-4 py-3">
                                        <span class="block text-sm text-gray-900">MVELE</span>
                                        <span
                                            class="block text-sm text-gray-500 truncate">alphonsemvele95@gmail.com</span>
                                    </div>
                                    <ul class="py-2" aria-labelledby="user-menu-button">
                                        <li>
                                            <a href="/logout"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Se
                                                déconnecter</a>
                                        </li>
                                        <form id="logout-form" action="" method="POST" class="hidden">
                                            @csrf
                                        </form>
                                    </ul>
                                </div>
                            </div>
                            <div class="hidden w-full md:block md:w-auto" id="navbar-dropdown">
                                <ul
                                    class="flex flex-col font-medium p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:space-x-8 rtl:space-x-reverse md:flex-row md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                                </ul>
                            </div>
                        </div>
                    </nav>
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script> -->
</body>

</html>