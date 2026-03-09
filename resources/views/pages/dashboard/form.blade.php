<?php

namespace App\Http\Livewire;

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;

name('form');
middleware(['auth', 'verified']);

new class extends Component {
    public $questions = [];
    public $selectedComponent = null;
    public $selectedIndex = null;
    public $successMessage = '';
    public $errorMessage = '';
    
    // Composants disponibles en dur
    public $availableComponents = [
        [
            'type' => 'text_input',
            'label' => 'Champ Texte',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>',
            'properties' => [
                'label' => 'Nouveau champ texte',
                'placeholder' => 'Entrez du texte',
                'required' => false,
                'maxLength' => ''
            ]
        ],
        [
            'type' => 'textarea',
            'label' => 'Zone de Texte',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>',
            'properties' => [
                'label' => 'Nouvelle zone de texte',
                'placeholder' => 'Entrez votre texte ici',
                'required' => false,
                'rows' => '4'
            ]
        ],
        [
            'type' => 'number_input',
            'label' => 'Nombre',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>',
            'properties' => [
                'label' => 'Nouveau champ nombre',
                'placeholder' => 'Entrez un nombre',
                'required' => false,
                'min' => '',
                'max' => ''
            ]
        ],
        [
            'type' => 'email',
            'label' => 'Email',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>',
            'properties' => [
                'label' => 'Adresse email',
                'placeholder' => 'exemple@email.com',
                'required' => false
            ]
        ],
        [
            'type' => 'dropdown',
            'label' => 'Liste Déroulante',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>',
            'properties' => [
                'label' => 'Nouvelle liste',
                'placeholder' => 'Sélectionnez une option',
                'required' => false,
                'options' => 'Option 1, Option 2, Option 3'
            ]
        ],
        [
            'type' => 'checkbox',
            'label' => 'Case à Cocher',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            'properties' => [
                'label' => 'Nouvelle case à cocher',
                'required' => false
            ]
        ],
        [
            'type' => 'radio',
            'label' => 'Bouton Radio',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            'properties' => [
                'label' => 'Nouveau groupe radio',
                'required' => false,
                'options' => 'Option 1, Option 2, Option 3'
            ]
        ],
        [
            'type' => 'date_picker',
            'label' => 'Date',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
            'properties' => [
                'label' => 'Sélectionnez une date',
                'required' => false
            ]
        ],
        [
            'type' => 'file_upload',
            'label' => 'Fichier',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>',
            'properties' => [
                'label' => 'Télécharger un fichier',
                'required' => false,
                'accept' => ''
            ]
        ],
        [
            'type' => 'phone',
            'label' => 'Téléphone',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>',
            'properties' => [
                'label' => 'Numéro de téléphone',
                'placeholder' => '+237 6XX XXX XXX',
                'required' => false
            ]
        ]
    ];

    public function addComponent($type)
    {
        $component = collect($this->availableComponents)->firstWhere('type', $type);
        if (!$component) {
            $this->errorMessage = 'Composant non trouvé.';
            return;
        }

        $this->questions[] = [
            'type' => $type,
            'properties' => $component['properties']
        ];

        $this->selectedIndex = count($this->questions) - 1;
        $this->selectedComponent = $type;
    }

    public function selectComponent($index)
    {
        if (!isset($this->questions[$index])) {
            $this->errorMessage = 'Composant invalide.';
            return;
        }
        $this->selectedIndex = $index;
        $this->selectedComponent = $this->questions[$index]['type'];
    }

    public function deleteComponent($index)
    {
        if (!isset($this->questions[$index])) {
            $this->errorMessage = 'Composant introuvable.';
            return;
        }
        array_splice($this->questions, $index, 1);
        if ($this->selectedIndex === $index) {
            $this->selectedComponent = null;
            $this->selectedIndex = null;
        } elseif ($this->selectedIndex > $index) {
            $this->selectedIndex--;
        }
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function duplicateComponent($index)
    {
        if (!isset($this->questions[$index])) {
            return;
        }
        $component = $this->questions[$index];
        $this->questions[] = $component;
    }
};
?>
<x-layouts.app title="STAT ENQUETE">
    @volt
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-8"
         data-signals="{
            selectedIndex: null,
            showPreview: false,
            isDragging: false,
            draggedIndex: null
         }">
        
        <!-- Include Datastar -->
        <script type="module" src="https://cdn.jsdelivr.net/gh/starfederation/datastar@main/bundles/datastar.js"></script>
        
        <!-- Include Hyperscript (Optional) -->
        <script src="https://unpkg.com/hyperscript.org@0.9.12"></script>

        <!-- Header -->
        <div class="max-w-7xl mx-auto mb-8"
             _="on load wait 100ms then add .fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 
                               transform transition-all duration-500"
                        _="on mouseenter add .scale-105 
                           on mouseleave remove .scale-105">
                        Créateur de Formulaires
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Glissez et déposez des composants pour créer votre formulaire personnalisé
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Preview Button with Datastar -->
                    <button 
                        data-on:click="$showPreview = !$showPreview"
                        data-class:bg-blue-600="$showPreview"
                        data-class:text-white="$showPreview"
                        class="px-6 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 
                               rounded-lg shadow-sm hover:shadow-md transition-all duration-200 
                               border border-gray-200 dark:border-gray-700"
                        _="on click transition opacity to 0 then to 1 over 300ms">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span data-text="$showPreview ? 'Fermer Aperçu' : 'Aperçu'">Aperçu</span>
                    </button>
                    
                    <!-- Save Button with Animation -->
                    <button 
                        wire:click="$dispatch('save-form')"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg shadow-sm 
                               hover:shadow-md hover:bg-blue-700 transition-all duration-200"
                        _="on click 
                           add .scale-95 
                           wait 100ms 
                           remove .scale-95 
                           add .animate-pulse 
                           wait 1s 
                           remove .animate-pulse">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>

        <!-- Messages with Auto-dismiss -->
        @if($successMessage)
            <div class="max-w-7xl mx-auto mb-6"
                 _="on load wait 3s then transition opacity to 0 over 500ms then remove me">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 
                            text-green-700 dark:text-green-300 px-6 py-4 rounded-xl shadow-sm flex items-center
                            transform transition-all duration-300"
                     _="on load add .slide-in-down">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ $successMessage }}
                </div>
            </div>
        @endif
        
        @if($errorMessage)
            <div class="max-w-7xl mx-auto mb-6"
                 _="on load wait 3s then transition opacity to 0 over 500ms then remove me">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 
                            text-red-700 dark:text-red-300 px-6 py-4 rounded-xl shadow-sm flex items-center
                            transform transition-all duration-300"
                     _="on load add .slide-in-down">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ $errorMessage }}
                </div>
            </div>
        @endif

        <!-- Preview Modal -->
        <div data-show="$showPreview" 
             style="display: none;"
             class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
             _="on click if target == me then set $showPreview to false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-8"
                 _="on load add .zoom-in"
                 data-on:click.stop="">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Aperçu du Formulaire
                    </h2>
                    <button data-on:click="$showPreview = false"
                            class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                            _="on click add .rotate-90 then wait 200ms then remove .rotate-90">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    @forelse($questions as $question)
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $question['properties']['label'] ?? 'Sans titre' }}
                                @if($question['properties']['required'] ?? false)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            @if($question['type'] === 'text_input')
                                <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                            @elseif($question['type'] === 'textarea')
                                <textarea rows="{{ $question['properties']['rows'] ?? 4 }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '' }}"></textarea>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                            Aucun composant à prévisualiser
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-5 gap-6">
                <!-- Left Sidebar: Components -->
                <div class="">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-8"
                         _="on load add .slide-in-left">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />
                            </svg>
                            Composants
                        </h2>
                        <div class="space-y-2 max-h-[calc(100vh-200px)] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                            @foreach($availableComponents as $component)
                                <button 
                                    wire:click="addComponent('{{ $component['type'] }}')" 
                                    class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl 
                                           hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 
                                           dark:hover:border-blue-700 border-2 border-transparent 
                                           transition-all duration-200 group"
                                    _="on click 
                                       add .scale-95 
                                       wait 100ms 
                                       remove .scale-95
                                       add .wiggle
                                       wait 500ms
                                       remove .wiggle">
                                    <div class="w-10 h-10 flex items-center justify-center bg-white dark:bg-gray-800 
                                                rounded-lg shadow-sm group-hover:shadow-md transition-shadow mr-3">
                                        <div class="w-5 h-5 text-gray-600 dark:text-gray-400 
                                                    group-hover:text-blue-600 dark:group-hover:text-blue-400 
                                                    transition-colors">
                                            {!! $component['icon'] !!}
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 
                                                 group-hover:text-blue-600 dark:group-hover:text-blue-400 
                                                 transition-colors">
                                        {{ $component['label'] }}
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center mt-4">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />
                            </svg>
                            Layout
                        </h2>
                    </div>
                </div>

                <!-- Center: Form Builder -->
                <div class="col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 min-h-[600px]"
                         _="on load add .fade-in-up">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Formulaire
                            </h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full"
                                  data-text="'{{ count($questions) }} composant' + ({{ count($questions) }} > 1 ? 's' : '')">
                                {{ count($questions) }} composant(s)
                            </span>
                        </div>

                        <div class="space-y-4"
                             data-signals="{componentCount: {{ count($questions) }}}">
                            @if(count($questions) > 0)
                                @foreach($questions as $index => $question)
                                    <div 
                                        wire:click="selectComponent({{ $index }})" 
                                        data-on:click="$selectedIndex = {{ $index }}"
                                        data-class:border-blue-400="$selectedIndex === {{ $index }}"
                                        data-class:bg-blue-50/50="$selectedIndex === {{ $index }}"
                                        data-class:shadow-md="$selectedIndex === {{ $index }}"
                                        class="group relative p-5 rounded-xl border-2 transition-all duration-200 cursor-pointer
                                            {{ $selectedIndex === $index 
                                                ? 'border-blue-400 dark:border-blue-600 bg-blue-50/50 dark:bg-blue-900/10 shadow-md' 
                                                : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-gray-50/30 dark:bg-gray-700/20' }}"
                                        draggable="true"
                                        _="on dragstart 
                                           set $isDragging to true
                                           set $draggedIndex to {{ $index }}
                                           add .opacity-50
                                           on dragend 
                                           set $isDragging to false
                                           remove .opacity-50
                                           on dragover halt the event
                                           add .border-blue-500
                                           on dragleave remove .border-blue-500
                                           on drop
                                           halt the event
                                           remove .border-blue-500">
                                        
                                        <!-- Component Content -->
                                        <div class="mb-3">
                                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                                {{ $question['properties']['label'] ?? 'Sans titre' }}
                                                @if($question['properties']['required'] ?? false)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>

                                            @if($question['type'] === 'text_input')
                                                <input type="text" 
                                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                              dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                              focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                                              transition-all duration-200" 
                                                       placeholder="{{ $question['properties']['placeholder'] ?? 'Entrez du texte' }}" 
                                                       disabled
                                                       _="on focus add .scale-105 on blur remove .scale-105">
                                            
                                            @elseif($question['type'] === 'textarea')
                                                <textarea rows="{{ $question['properties']['rows'] ?? 4 }}" 
                                                          class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                                 dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                                 focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                                                 transition-all duration-200" 
                                                          placeholder="{{ $question['properties']['placeholder'] ?? 'Entrez votre texte' }}" 
                                                          disabled></textarea>
                                            
                                            @elseif($question['type'] === 'number_input')
                                                <input type="number" 
                                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                              dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                              focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       placeholder="{{ $question['properties']['placeholder'] ?? 'Entrez un nombre' }}" 
                                                       disabled>
                                            
                                            @elseif($question['type'] === 'email')
                                                <input type="email" 
                                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                              dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                              focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       placeholder="{{ $question['properties']['placeholder'] ?? 'exemple@email.com' }}" 
                                                       disabled>
                                            
                                            @elseif($question['type'] === 'phone')
                                                <input type="tel" 
                                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                              dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                              focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       placeholder="{{ $question['properties']['placeholder'] ?? '+237 6XX XXX XXX' }}" 
                                                       disabled>
                                            
                                            @elseif($question['type'] === 'dropdown')
                                                <select class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                               dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                               focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                        disabled>
                                                    <option>{{ $question['properties']['placeholder'] ?? 'Sélectionnez une option' }}</option>
                                                </select>
                                            
                                            @elseif($question['type'] === 'checkbox')
                                                <div class="flex items-center">
                                                    <input type="checkbox" 
                                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded 
                                                                  focus:ring-blue-500" 
                                                           disabled>
                                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                                        Case à cocher
                                                    </span>
                                                </div>
                                            
                                            @elseif($question['type'] === 'radio')
                                                <div class="space-y-2">
                                                    @foreach(explode(',', $question['properties']['options'] ?? 'Option 1,Option 2') as $option)
                                                        <div class="flex items-center">
                                                            <input type="radio" 
                                                                   name="radio-{{ $index }}" 
                                                                   class="w-4 h-4 text-blue-600 border-gray-300 
                                                                          focus:ring-blue-500" 
                                                                   disabled>
                                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                                                {{ trim($option) }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            
                                            @elseif($question['type'] === 'date_picker')
                                                <input type="date" 
                                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                              dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                              focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       disabled>
                                            
                                            @elseif($question['type'] === 'file_upload')
                                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 
                                                            rounded-lg p-6 text-center transition-colors duration-200
                                                            hover:border-blue-400 dark:hover:border-blue-600">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                        Cliquez pour télécharger ou glissez un fichier
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="absolute top-3 right-3 flex space-x-2 opacity-0 group-hover:opacity-100 
                                                    transition-opacity duration-200"
                                             _="on load add .slide-in-right">
                                            <button 
                                                wire:click.stop="duplicateComponent({{ $index }})"
                                                class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md 
                                                       text-gray-600 dark:text-gray-400 hover:text-blue-600 
                                                       dark:hover:text-blue-400 transition-all"
                                                _="on click 
                                                   add .rotate-12 
                                                   wait 100ms 
                                                   remove .rotate-12">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                            <button 
                                                wire:click.stop="deleteComponent({{ $index }})"
                                                class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md 
                                                       text-gray-600 dark:text-gray-400 hover:text-red-600 
                                                       dark:hover:text-red-400 transition-all"
                                                _="on click 
                                                   add .shake 
                                                   wait 300ms 
                                                   remove .shake">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-16"
                                     _="on load add .fade-in">
                                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full 
                                                flex items-center justify-center"
                                         _="on mouseenter add .bounce on mouseleave remove .bounce">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        Aucun composant ajouté
                                    </h3>
                                    <p class="text-gray-500 dark:text-gray-400 mb-6">
                                        Commencez par ajouter des composants depuis la barre latérale
                                    </p>
                                    <div class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 
                                                text-blue-600 dark:text-blue-400 rounded-lg text-sm"
                                         _="on load add .pulse-slow">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Cliquez sur un composant à gauche pour l'ajouter
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Properties -->
                <div class="">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-8"
                         _="on load add .slide-in-right">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Propriétés
                        </h2>

                        @if($selectedComponent && $selectedIndex !== null && isset($questions[$selectedIndex]))
                            <div class="space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto pr-2 
                                        scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600"
                                 _="on load add .fade-in-up">
                                <!-- Label -->
                                <div _="on load add .slide-in-right">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Libellé du champ
                                    </label>
                                    <input 
                                        wire:model.live="questions.{{ $selectedIndex }}.properties.label" 
                                        type="text" 
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                               dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                               focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        placeholder="Entrez le libellé"
                                        _="on focus add .scale-105 on blur remove .scale-105">
                                </div>

                                <!-- Placeholder (if applicable) -->
                                @if(in_array($questions[$selectedIndex]['type'], ['text_input', 'textarea', 'number_input', 'email', 'phone', 'dropdown']))
                                    <div _="on load add .slide-in-right">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Texte indicatif
                                        </label>
                                        <input 
                                            wire:model.live="questions.{{ $selectedIndex }}.properties.placeholder" 
                                            type="text" 
                                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                   dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="Texte d'aide">
                                    </div>
                                @endif

                                <!-- Required Checkbox -->
                                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                     _="on load add .slide-in-right">
                                    <input 
                                        wire:model.live="questions.{{ $selectedIndex }}.properties.required" 
                                        type="checkbox" 
                                        id="required-checkbox"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        _="on change if I'm checked add .animate-bounce to next <label/> 
                                           wait 500ms 
                                           remove .animate-bounce from next <label/>">
                                    <label for="required-checkbox" 
                                           class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Champ obligatoire
                                    </label>
                                </div>

                                <!-- Textarea Rows -->
                                @if($questions[$selectedIndex]['type'] === 'textarea')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Nombre de lignes
                                        </label>
                                        <input 
                                            wire:model.live="questions.{{ $selectedIndex }}.properties.rows" 
                                            type="number" 
                                            min="2" 
                                            max="10"
                                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                   dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    </div>
                                @endif

                                <!-- Number Input Min/Max -->
                                @if($questions[$selectedIndex]['type'] === 'number_input')
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Min
                                            </label>
                                            <input 
                                                wire:model.live="questions.{{ $selectedIndex }}.properties.min" 
                                                type="number" 
                                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                       dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                       focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="0">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Max
                                            </label>
                                            <input 
                                                wire:model.live="questions.{{ $selectedIndex }}.properties.max" 
                                                type="number" 
                                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                       dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                       focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                placeholder="100">
                                        </div>
                                    </div>
                                @endif

                                <!-- Text Input Max Length -->
                                @if($questions[$selectedIndex]['type'] === 'text_input')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Longueur maximale
                                        </label>
                                        <input 
                                            wire:model.live="questions.{{ $selectedIndex }}.properties.maxLength" 
                                            type="number" 
                                            min="1"
                                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                   dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="Ex: 100">
                                    </div>
                                @endif

                                <!-- Options for Dropdown/Radio -->
                                @if(in_array($questions[$selectedIndex]['type'], ['dropdown', 'radio']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Options (séparées par virgule)
                                        </label>
                                        <textarea 
                                            wire:model.live="questions.{{ $selectedIndex }}.properties.options" 
                                            rows="3"
                                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                   dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder="Option 1, Option 2, Option 3"></textarea>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Séparez chaque option par une virgule
                                        </p>
                                    </div>
                                @endif

                                <!-- File Accept -->
                                @if($questions[$selectedIndex]['type'] === 'file_upload')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Types de fichiers acceptés
                                        </label>
                                        <input 
                                            wire:model.live="questions.{{ $selectedIndex }}.properties.accept" 
                                            type="text" 
                                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 
                                                   dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                                   focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                            placeholder=".pdf, .doc, .jpg">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Ex: .pdf, .doc, image/*
                                        </p>
                                    </div>
                                @endif

                                <!-- Component Info -->
                                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                     _="on load add .pulse-slow">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-blue-900 dark:text-blue-300">
                                                Type: {{ ucfirst(str_replace('_', ' ', $questions[$selectedIndex]['type'])) }}
                                            </p>
                                            <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                                                Modifiez les propriétés pour mettre à jour le composant
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12"
                                 _="on load add .fade-in">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full 
                                            flex items-center justify-center"
                                     _="on mouseenter add .wiggle on mouseleave remove .wiggle">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                    Sélectionnez un composant dans le formulaire pour modifier ses propriétés
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Scrollbar Styles -->
        <style>
            .scrollbar-thin::-webkit-scrollbar {
                width: 6px;
            }
            .scrollbar-thin::-webkit-scrollbar-track {
                background: transparent;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb {
                background: #cbd5e0;
                border-radius: 3px;
            }
            .dark .scrollbar-thin::-webkit-scrollbar-thumb {
                background: #4a5568;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb:hover {
                background: #a0aec0;
            }
            .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
                background: #5a6c7d;
            }

            /* Custom Animations */
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes slideInLeft {
                from {
                    opacity: 0;
                    transform: translateX(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes zoomIn {
                from {
                    opacity: 0;
                    transform: scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            
            @keyframes wiggle {
                0%, 100% { transform: rotate(0deg); }
                25% { transform: rotate(-3deg); }
                75% { transform: rotate(3deg); }
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            
            @keyframes pulseSlow {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.7; }
            }

            .fade-in {
                animation: fadeIn 0.5s ease-in;
            }
            
            .fade-in-up {
                animation: fadeInUp 0.5s ease-out;
            }
            
            .slide-in-left {
                animation: slideInLeft 0.5s ease-out;
            }
            
            .slide-in-right {
                animation: slideInRight 0.5s ease-out;
            }
            
            .slide-in-down {
                animation: slideInDown 0.5s ease-out;
            }
            
            .zoom-in {
                animation: zoomIn 0.3s ease-out;
            }
            
            .wiggle {
                animation: wiggle 0.5s ease-in-out;
            }
            
            .shake {
                animation: shake 0.3s ease-in-out;
            }
            
            .pulse-slow {
                animation: pulseSlow 2s ease-in-out infinite;
            }
        </style>
    </div>
    @endvolt
</x-layouts.app>