
    <div class="flex min-h-screen">
        <!-- Sidebar corrigée -->
        <div class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 dark:bg-gray-950 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col">
            <!-- Header de la sidebar -->
            <div class="flex items-center justify-between p-4 border-b border-gray-700 flex-shrink-0">
                <div class="flex items-center space-x-2">
                                                    <img src="{{ asset('logocamtel.png') }}" class="h-16" alt="Flowbite Logo" />

                    <h2 class="text-xl font-bold"> Form Panel</h2>
                </div>
                <button class="lg:hidden text-white hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation Menu - avec flex-1 pour occuper l'espace restant -->
            <nav class="p-4 flex-1 flex flex-col">
                <ul class="space-y-2 flex-1">
                    <!-- Accueil -->
                    <li>
                        <a href="/admin" class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group">
                            <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="font-medium">Home</span>
                        </a>
                    </li>

                    <!-- Gestion des Types (Page actuelle) -->
                    <li>
                        <a href="/admin/type" class="flex items-center p-3 rounded-lg  hover:bg-blue-700 transition-colors duration-200 group">
                            <svg class="w-6 h-6 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H3m16 8H7m12 4H9"></path>
                            </svg>
                            <span class="font-medium text-white">Types</span>
                        </a>
                    </li>


                      <!-- Gestion des Types (Page actuelle) -->
                    <li>
                        <a href="/admin/property" class="flex items-center p-3 rounded-lg  hover:bg-blue-700 transition-colors duration-200 group">
                            <svg class="w-6 h-6 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H3m16 8H7m12 4H9"></path>
                            </svg>
                            <span class="font-medium text-white">Property</span>
                        </a>
                    </li>
                    <!-- Utilisateurs -->
                    <li>
                        <a href="/admin/users" class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group">
                            <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <span class="font-medium">Users</span>
                        </a>
                    </li>

                    <!-- Formulaires -->
                    <li>
                        <a href="/admin/forms" class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group">
                            <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="font-medium">Form</span>
                        </a>
                    </li>

                    <!-- Statistiques -->
                    <li>
                        <a href="/admin/stats" class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group">
                            <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="font-medium">Statistiques</span>
                        </a>
                    </li>

                    <!-- Paramètres -->
                    <li>
                        <a href="/admin/settings" class="flex items-center p-3 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors duration-200 group">
                            <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-medium">Paramètres</span>
                        </a>
                    </li>
                </ul>

                <!-- Section de déconnexion - fixée en bas -->
                <div class="mt-8 pt-4 border-t border-gray-700">
                    <a href="/logout" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition-colors duration-200 group">
                        <svg class="w-6 h-6 mr-3 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="font-medium">Déconnexion</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Contenu principal pour démonstration -->
       
    </div>
