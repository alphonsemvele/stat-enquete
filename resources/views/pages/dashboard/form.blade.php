<?php

namespace App\Http\Livewire;

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Form;
use App\Models\FormQuestion;

name('form');
middleware(['auth', 'verified']);

new class extends Component {

    // ── État du formulaire ─────────────────────────────────────────────────
    public ?int  $formId       = null;
    public string $formTitle   = 'Nouveau formulaire';
    public bool   $editingTitle  = false;
    public ?string $formReference = null;

    public $questions         = [];
    public $selectedComponent = null;
    public $selectedIndex     = null;
    public $successMessage    = '';
    public $errorMessage      = '';
    public $showPreview       = false;

    // ── Cycle de vie ────────────────────────────────────────────────────────
    public function mount(?int $id = null): void
    {
        if ($id) {
            $form = Form::with(['questions' => fn($q) => $q->orderBy('order')])->findOrFail($id);
            $this->authorize('update', $form);

            $this->formId       = $form->id;
            $this->formTitle    = $form->title;
            $this->formReference = $form->reference ?? '';
            $this->questions = $form->questions
                ->map(fn($q) => ['type' => $q->type, 'properties' => $q->properties ?? []])
                ->toArray();
        }
    }

    // ── Composants disponibles ───────────────────────────────────────────
    public $availableComponents = [
        [
            'type' => 'text_input',
            'label' => 'Champ Texte',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>',
            'properties' => ['label' => 'Nouveau champ texte', 'placeholder' => 'Entrez du texte', 'required' => false, 'maxLength' => '']
        ],
        [
            'type' => 'textarea',
            'label' => 'Zone de Texte',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>',
            'properties' => ['label' => 'Nouvelle zone de texte', 'placeholder' => 'Entrez votre texte ici', 'required' => false, 'rows' => '4']
        ],
        [
            'type' => 'number_input',
            'label' => 'Nombre',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>',
            'properties' => ['label' => 'Nouveau champ nombre', 'placeholder' => 'Entrez un nombre', 'required' => false, 'min' => '', 'max' => '']
        ],
        [
            'type' => 'email',
            'label' => 'Email',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>',
            'properties' => ['label' => 'Adresse email', 'placeholder' => 'exemple@email.com', 'required' => false]
        ],
        [
            'type' => 'dropdown',
            'label' => 'Liste Déroulante',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>',
            'properties' => ['label' => 'Nouvelle liste', 'placeholder' => 'Sélectionnez une option', 'required' => false, 'options' => 'Option 1, Option 2, Option 3']
        ],
        [
            'type' => 'checkbox',
            'label' => 'Case à Cocher',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            'properties' => ['label' => 'Nouvelle case à cocher', 'required' => false]
        ],
        [
            'type' => 'radio',
            'label' => 'Bouton Radio',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            'properties' => ['label' => 'Nouveau groupe radio', 'required' => false, 'options' => 'Option 1, Option 2, Option 3']
        ],
        [
            'type' => 'date_picker',
            'label' => 'Date',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
            'properties' => ['label' => 'Sélectionnez une date', 'required' => false]
        ],
        [
            'type' => 'file_upload',
            'label' => 'Fichier',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>',
            'properties' => ['label' => 'Télécharger un fichier', 'required' => false, 'accept' => '']
        ],
        [
            'type' => 'phone',
            'label' => 'Téléphone',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>',
            'properties' => ['label' => 'Numéro de téléphone', 'placeholder' => '+237 6XX XXX XXX', 'required' => false]
        ],
        [
            'type' => 'block_title',
            'label' => 'Titre',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>',
            'properties' => ['title' => 'Nouveau titre', 'subtitle' => '', 'size' => 'h2']
        ],
    ];

    // ── Méthodes composants ────────────────────────────────────────────────

    public function addComponent($type)
    {
        $component = collect($this->availableComponents)->firstWhere('type', $type);
        if (!$component) { $this->errorMessage = 'Composant non trouvé.'; return; }
        $this->questions[] = ['type' => $type, 'properties' => $component['properties']];
        $this->selectedIndex = count($this->questions) - 1;
        $this->selectedComponent = $type;
    }

    public function selectComponent($index)
    {
        if (!isset($this->questions[$index])) { $this->errorMessage = 'Composant invalide.'; return; }
        $this->selectedIndex = $index;
        $this->selectedComponent = $this->questions[$index]['type'];
    }

    public function deleteComponent($index)
    {
        if (!isset($this->questions[$index])) { $this->errorMessage = 'Composant introuvable.'; return; }
        array_splice($this->questions, $index, 1);
        if ($this->selectedIndex === $index) { $this->selectedComponent = null; $this->selectedIndex = null; }
        elseif ($this->selectedIndex > $index) { $this->selectedIndex--; }
    }

    public function updateProperty($index, $key, $value)
    {
        if (!isset($this->questions[$index])) return;
        $questions = $this->questions;
        $questions[$index]['properties'][$key] = $value;
        $this->questions = $questions;
    }

    public function moveUp($index)
    {
        if ($index <= 0 || !isset($this->questions[$index])) return;
        $questions = $this->questions;
        [$questions[$index - 1], $questions[$index]] = [$questions[$index], $questions[$index - 1]];
        $this->questions = $questions;
        if ($this->selectedIndex === $index) $this->selectedIndex = $index - 1;
        elseif ($this->selectedIndex === $index - 1) $this->selectedIndex = $index;
    }

    public function moveDown($index)
    {
        if ($index >= count($this->questions) - 1 || !isset($this->questions[$index])) return;
        $questions = $this->questions;
        [$questions[$index], $questions[$index + 1]] = [$questions[$index + 1], $questions[$index]];
        $this->questions = $questions;
        if ($this->selectedIndex === $index) $this->selectedIndex = $index + 1;
        elseif ($this->selectedIndex === $index + 1) $this->selectedIndex = $index;
    }

    public function duplicateComponent($index)
    {
        if (!isset($this->questions[$index])) return;
        $this->questions[] = $this->questions[$index];
    }

    // ── Titre éditable ──────────────────────────────────────────────────────
    public function startEditTitle(): void { $this->editingTitle = true; }
    public function stopEditTitle(): void
    {
        $this->editingTitle = false;
        $this->formTitle = trim($this->formTitle) ?: 'Nouveau formulaire';
    }

    // ── Sauvegarde DB ───────────────────────────────────────────────────────
    public function saveForm(): void
    {
        $this->validate([
            'formTitle' => 'required|string|max:255',
            'questions' => 'array',
        ]);

        try {
            // 1. Créer ou mettre à jour le formulaire
            if ($this->formId) {
                $form = Form::findOrFail($this->formId);
                $this->authorize('update', $form);
                $form->title = trim($this->formTitle);
                $form->save();
            } else {
                // Génération de la référence unique ici (fallback si boot() du modèle ne suffit pas)
                do {
                    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
                    $rand  = '';
                    for ($i = 0; $i < 6; $i++) {
                        $rand .= $chars[random_int(0, strlen($chars) - 1)];
                    }
                    $ref = 'FORM-' . date('Y') . '-' . $rand;
                } while (\App\Models\Form::withTrashed()->where('reference', $ref)->exists());

                $form = Form::create([
                    'user_id'   => auth()->id(),
                    'title'     => trim($this->formTitle),
                    'reference' => $ref,
                ]);
                $this->formId        = $form->id;
                $this->formReference = $form->reference;
            }

            // 2. Supprimer les anciennes questions et réinsérer dans l'ordre
            $form->questions()->delete();

            foreach ($this->questions as $order => $q) {
                FormQuestion::create([
                    'form_id'    => $form->id,
                    'type'       => $q['type'],
                    'properties' => $q['properties'] ?? [],
                    'order'      => $order,
                ]);
            }

            $this->successMessage = 'Formulaire enregistré avec succès !';
            $this->errorMessage   = '';

        } catch (\Illuminate\Auth\Access\AuthorizationException) {
            $this->errorMessage   = "Vous n'êtes pas autorisé à modifier ce formulaire.";
            $this->successMessage = '';
        } catch (\Throwable $e) {
            $this->errorMessage   = "Erreur lors de l'enregistrement : " . $e->getMessage();
            $this->successMessage = '';
        }
    }

    // ── Publication ─────────────────────────────────────────────────────────
    public function publishForm(): void
    {
        if (!$this->formId) {
            $this->saveForm();
            if ($this->errorMessage) return;
        }

        try {
            $form = Form::findOrFail($this->formId);
            $this->authorize('update', $form);
            $form->update(['is_published' => true]);
            $this->successMessage = 'Formulaire publié avec succès !';
            $this->errorMessage   = '';
        } catch (\Throwable $e) {
            $this->errorMessage = "Erreur lors de la publication : " . $e->getMessage();
        }
    }

    public function togglePreview(): void { $this->showPreview = !$this->showPreview; }
    public function resetMessages(): void { $this->successMessage = ''; $this->errorMessage = ''; }
};
?>
<x-layouts.app title="STAT ENQUETE">
    @volt
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-8">

        <!-- ══ Header ══ -->
        <div class="max-w-7xl mx-auto mb-8">
            <div class="flex items-center justify-between gap-4">

                <!-- Titre éditable inline -->
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    @if($editingTitle)
                        <input
                            wire:model.live="formTitle"
                            wire:blur="stopEditTitle"
                            wire:keydown.enter="stopEditTitle"
                            type="text"
                            autofocus
                            class="text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0
                                   border-b-2 border-blue-400 outline-none focus:ring-0 flex-1 min-w-0 pb-0.5">
                    @else
                        <h1
                            wire:click="startEditTitle"
                            title="Cliquer pour modifier le titre"
                            class="text-2xl font-bold text-gray-900 dark:text-white cursor-text truncate
                                   hover:text-blue-600 dark:hover:text-blue-400 transition-colors group flex items-center gap-2">
                            {{ $formTitle }}
                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-40 transition-opacity shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.586-6.586a2 2 0 112.828 2.828L11.828 13.828A2 2 0 0111 14H9v-2a2 2 0 01.586-1.414z"/>
                            </svg>
                        </h1>
                    @endif

                    @if($formId)
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-medium px-2.5 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full">
                                Enregistré
                            </span>
                            @if($formReference)
                            <span class="text-xs font-mono px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-full border border-gray-200 dark:border-gray-600 select-all" title="Référence du formulaire">
                                {{ $formReference }}
                            </span>
                            @endif
                        </div>
                    @else
                        <span class="shrink-0 text-xs font-medium px-2.5 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full">
                            Non enregistré
                        </span>
                    @endif
                </div>

                <!-- Actions header -->
                <div class="flex items-center space-x-3 shrink-0">

                    <button wire:click="togglePreview"
                            class="px-5 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                                   rounded-lg shadow-sm hover:shadow-md transition-all duration-200
                                   border border-gray-200 dark:border-gray-700
                                   {{ $showPreview ? '!bg-blue-600 !text-white !border-blue-600' : '' }}">
                        <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ $showPreview ? 'Fermer Aperçu' : 'Aperçu' }}
                    </button>

                    <!-- Enregistrer -->
                    <button wire:click="saveForm" wire:loading.attr="disabled" wire:target="saveForm"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                                   rounded-lg shadow-sm hover:shadow-md transition-all duration-200
                                   border border-gray-200 dark:border-gray-700
                                   hover:border-blue-300 hover:text-blue-600 disabled:opacity-60">
                        <svg wire:loading wire:target="saveForm" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <svg wire:loading.remove wire:target="saveForm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span wire:loading wire:target="saveForm">Sauvegarde…</span>
                        <span wire:loading.remove wire:target="saveForm">Enregistrer</span>
                    </button>

                    <!-- Publier -->
                    <button wire:click="publishForm" wire:loading.attr="disabled" wire:target="publishForm"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-blue-600 text-white rounded-lg shadow-sm
                                   hover:bg-blue-700 hover:shadow-md transition-all duration-200 disabled:opacity-60">
                        <svg wire:loading wire:target="publishForm" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <svg wire:loading.remove wire:target="publishForm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span wire:loading wire:target="publishForm">Publication…</span>
                        <span wire:loading.remove wire:target="publishForm">Publier</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ Messages flash ══ -->
        @if($successMessage)
            <div class="max-w-7xl mx-auto mb-6" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ $successMessage }}
                </div>
            </div>
        @endif
        @if($errorMessage)
            <div class="max-w-7xl mx-auto mb-6" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ $errorMessage }}
                </div>
            </div>
        @endif

        <!-- ══ Modal Aperçu ══ -->
        @if($showPreview)
            <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                 wire:click.self="togglePreview">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-8">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $formTitle }}</h2>
                            <p class="text-sm text-gray-400 mt-0.5">Aperçu · non interactif</p>
                        </div>
                        <button wire:click="togglePreview" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        @forelse($questions as $question)
                            @if($question['type'] === 'block_title')
                                @php $sz = match($question['properties']['size'] ?? 'h2') { 'h1'=>'text-3xl','h2'=>'text-2xl','h3'=>'text-xl',default=>'text-2xl' }; @endphp
                                <div class="py-2">
                                    <p class="{{ $sz }} font-bold text-gray-900 dark:text-white">{{ $question['properties']['title'] ?? 'Titre' }}</p>
                                    @if($question['properties']['subtitle'] ?? '')<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $question['properties']['subtitle'] }}</p>@endif
                                </div>
                            @else
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ $question['properties']['label'] ?? 'Sans titre' }}
                                        @if($question['properties']['required'] ?? false)<span class="text-red-500 ml-0.5">*</span>@endif
                                    </label>
                                    @if($question['type'] === 'text_input')
                                        <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                    @elseif($question['type'] === 'textarea')
                                        <textarea rows="{{ $question['properties']['rows'] ?? 4 }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '' }}"></textarea>
                                    @elseif($question['type'] === 'number_input')
                                        <input type="number" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                    @elseif($question['type'] === 'email')
                                        <input type="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                    @elseif($question['type'] === 'phone')
                                        <input type="tel" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                    @elseif($question['type'] === 'dropdown')
                                        <select class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                            <option>{{ $question['properties']['placeholder'] ?? 'Sélectionnez' }}</option>
                                            @foreach(explode(',', $question['properties']['options'] ?? '') as $opt)<option>{{ trim($opt) }}</option>@endforeach
                                        </select>
                                    @elseif($question['type'] === 'checkbox')
                                        <div class="flex items-center gap-2"><input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded"><span class="text-sm text-gray-600 dark:text-gray-400">Case à cocher</span></div>
                                    @elseif($question['type'] === 'radio')
                                        <div class="space-y-2">
                                            @foreach(explode(',', $question['properties']['options'] ?? 'Option 1') as $opt)
                                                <div class="flex items-center gap-2"><input type="radio" name="prev_{{ $loop->parent->index }}" class="w-4 h-4 text-blue-600"><span class="text-sm text-gray-600 dark:text-gray-400">{{ trim($opt) }}</span></div>
                                            @endforeach
                                        </div>
                                    @elseif($question['type'] === 'date_picker')
                                        <input type="date" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    @elseif($question['type'] === 'file_upload')
                                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                            <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                            <p class="text-sm text-gray-500">Glissez un fichier ou cliquez</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @empty
                            <p class="text-center text-gray-400 py-8">Aucun composant ajouté</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <!-- ══ Layout principal ══ -->
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-5 gap-6">

                <!-- ── Sidebar gauche ── -->
                <div class="">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-8">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z" />
                            </svg>
                            Composants
                        </h2>
                        <div class="space-y-2 max-h-[40vh] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
                            @foreach($availableComponents as $component)
                                <button wire:click="addComponent('{{ $component['type'] }}')"
                                    class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl
                                           hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-300 dark:hover:border-blue-700
                                           border-2 border-transparent transition-all duration-200 group">
                                    <div class="w-10 h-10 flex items-center justify-center bg-white dark:bg-gray-800 rounded-lg shadow-sm group-hover:shadow-md transition-shadow mr-3 shrink-0">
                                        <div class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {!! $component['icon'] !!}
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors text-left">
                                        {{ $component['label'] }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ── Centre : Builder ── -->
                <div class="col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 min-h-[600px]">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Formulaire
                            </h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full">
                                {{ count($questions) }} composant(s)
                            </span>
                        </div>

                        <div class="space-y-4">
                            @if(count($questions) > 0)
                                @foreach($questions as $index => $question)

                                    {{-- ── block_title ── --}}
                                    @if($question['type'] === 'block_title')
                                        @php $titleSize = match($question['properties']['size'] ?? 'h2') { 'h1'=>'text-3xl','h2'=>'text-2xl','h3'=>'text-xl',default=>'text-2xl' }; @endphp
                                        <div wire:click="selectComponent({{ $index }})" wire:key="question-{{ $index }}"
                                             class="group relative rounded-xl border-2 transition-all duration-200 cursor-pointer px-6 py-5
                                                {{ $selectedIndex === $index ? 'border-violet-400 dark:border-violet-600 shadow-md bg-violet-50/30 dark:bg-violet-900/10' : 'border-gray-200 dark:border-gray-700 hover:border-violet-300 dark:hover:border-violet-700 bg-white dark:bg-gray-800' }}">
                                            <span class="absolute top-2 left-3 text-xs font-mono text-violet-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">{{ strtoupper($question['properties']['size'] ?? 'H2') }}</span>
                                            <input wire:click.stop wire:input="updateProperty({{ $index }}, 'title', $event.target.value)" value="{{ $question['properties']['title'] ?? '' }}" type="text" placeholder="Titre…"
                                                   class="w-full {{ $titleSize }} font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-transparent hover:border-gray-200 dark:hover:border-gray-600 focus:border-violet-400 dark:focus:border-violet-500 outline-none focus:ring-0 transition-colors pb-0.5 cursor-text placeholder-gray-300 dark:placeholder-gray-600">
                                            <input wire:click.stop wire:input="updateProperty({{ $index }}, 'subtitle', $event.target.value)" value="{{ $question['properties']['subtitle'] ?? '' }}" type="text" placeholder="Sous-titre (optionnel)…"
                                                   class="w-full mt-1 text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 outline-none focus:ring-0 cursor-text placeholder-gray-300 dark:placeholder-gray-600">
                                            <div class="absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <button wire:click.stop="moveUp({{ $index }})" @if($index === 0) disabled @endif class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-gray-700 transition-all disabled:opacity-30">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                                </button>
                                                <button wire:click.stop="moveDown({{ $index }})" @if($index === count($questions) - 1) disabled @endif class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-gray-700 transition-all disabled:opacity-30">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <div class="w-px h-4 bg-gray-200 dark:bg-gray-600 mx-0.5 self-center"></div>
                                                <button wire:click.stop="selectComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-violet-600 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                </button>
                                                <button wire:click.stop="duplicateComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-blue-600 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                </button>
                                                <button wire:click.stop="deleteComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-red-600 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </div>

                                    {{-- ── Champs ── --}}
                                    @else
                                        <div wire:click="selectComponent({{ $index }})" wire:key="question-{{ $index }}"
                                             class="group relative p-5 rounded-xl border-2 transition-all duration-200 cursor-pointer
                                                {{ $selectedIndex === $index ? 'border-blue-400 dark:border-blue-600 bg-blue-50/50 dark:bg-blue-900/10 shadow-md' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-gray-50/30 dark:bg-gray-700/20' }}">
                                            <div class="mb-3">
                                                <div class="flex items-center mb-2 gap-1">
                                                    <input wire:click.stop wire:input="updateProperty({{ $index }}, 'label', $event.target.value)" value="{{ $question['properties']['label'] ?? '' }}" type="text" placeholder="Libellé du champ…"
                                                           class="flex-1 text-sm font-semibold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-transparent hover:border-gray-200 dark:hover:border-gray-600 focus:border-blue-400 dark:focus:border-blue-500 outline-none focus:ring-0 transition-colors pb-0.5 cursor-text placeholder-gray-300 dark:placeholder-gray-600">
                                                    @if($question['properties']['required'] ?? false)<span class="text-red-500 font-bold text-sm shrink-0">*</span>@endif
                                                </div>
                                                @if($question['type'] === 'text_input')
                                                    <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? 'Entrez du texte' }}" disabled>
                                                @elseif($question['type'] === 'textarea')
                                                    <textarea rows="{{ $question['properties']['rows'] ?? 4 }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? 'Entrez votre texte' }}" disabled></textarea>
                                                @elseif($question['type'] === 'number_input')
                                                    <input type="number" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? 'Entrez un nombre' }}" disabled>
                                                @elseif($question['type'] === 'email')
                                                    <input type="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? 'exemple@email.com' }}" disabled>
                                                @elseif($question['type'] === 'phone')
                                                    <input type="tel" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="{{ $question['properties']['placeholder'] ?? '+237 6XX XXX XXX' }}" disabled>
                                                @elseif($question['type'] === 'dropdown')
                                                    <select class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" disabled>
                                                        <option>{{ $question['properties']['placeholder'] ?? 'Sélectionnez une option' }}</option>
                                                    </select>
                                                @elseif($question['type'] === 'checkbox')
                                                    <div class="flex items-center">
                                                        <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded" disabled>
                                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Case à cocher</span>
                                                    </div>
                                                @elseif($question['type'] === 'radio')
                                                    <div class="space-y-2">
                                                        @foreach(explode(',', $question['properties']['options'] ?? 'Option 1,Option 2') as $option)
                                                            <div class="flex items-center">
                                                                <input type="radio" name="radio-{{ $index }}" class="w-4 h-4 text-blue-600 border-gray-300" disabled>
                                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ trim($option) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif($question['type'] === 'date_picker')
                                                    <input type="date" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" disabled>
                                                @elseif($question['type'] === 'file_upload')
                                                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Glissez ou cliquez pour télécharger</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="absolute top-3 right-3 flex space-x-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <button wire:click.stop="moveUp({{ $index }})" @if($index === 0) disabled @endif class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-gray-700 transition-all disabled:opacity-30">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                                </button>
                                                <button wire:click.stop="moveDown({{ $index }})" @if($index === count($questions) - 1) disabled @endif class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-gray-700 transition-all disabled:opacity-30">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <div class="w-px h-4 bg-gray-200 dark:bg-gray-600 mx-0.5 self-center"></div>
                                                <button wire:click.stop="selectComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-violet-600 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                </button>
                                                <button wire:click.stop="duplicateComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-blue-600 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                </button>
                                                <button wire:click.stop="deleteComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md text-gray-500 hover:text-red-600 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                @endforeach
                            @else
                                <div class="text-center py-16">
                                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Aucun composant ajouté</h3>
                                    <p class="text-gray-500 dark:text-gray-400 mb-6">Commencez par ajouter des composants depuis la barre latérale</p>
                                    <div class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg text-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        Cliquez sur un composant à gauche pour l'ajouter
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ── Sidebar droite : Propriétés ── -->
                <div class="">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-8">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Propriétés
                        </h2>

                        @if($selectedComponent && $selectedIndex !== null && isset($questions[$selectedIndex]))
                            <div class="space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600"
                                 wire:key="props-{{ $selectedIndex }}">

                                @if($questions[$selectedIndex]['type'] === 'block_title')
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Titre</label>
                                        <input wire:input="updateProperty({{ $selectedIndex }}, 'title', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['title'] ?? '' }}" type="text"
                                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Titre principal">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sous-titre <span class="text-gray-400 font-normal">(optionnel)</span></label>
                                        <input wire:input="updateProperty({{ $selectedIndex }}, 'subtitle', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['subtitle'] ?? '' }}" type="text"
                                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="Sous-titre">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Taille</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach(['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3'] as $val => $lab)
                                                <button wire:click="updateProperty({{ $selectedIndex }}, 'size', '{{ $val }}')"
                                                        class="py-2 px-3 rounded-lg border-2 text-sm font-medium transition-all
                                                            {{ ($questions[$selectedIndex]['properties']['size'] ?? 'h2') === $val ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-violet-300' }}">
                                                    {{ $lab }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                @else
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Libellé du champ</label>
                                        <input wire:input="updateProperty({{ $selectedIndex }}, 'label', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['label'] ?? '' }}" type="text"
                                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Entrez le libellé">
                                    </div>

                                    @if(in_array($questions[$selectedIndex]['type'], ['text_input', 'textarea', 'number_input', 'email', 'phone', 'dropdown']))
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Texte indicatif</label>
                                            <input wire:input="updateProperty({{ $selectedIndex }}, 'placeholder', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['placeholder'] ?? '' }}" type="text"
                                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Texte d'aide">
                                        </div>
                                    @endif

                                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <input wire:change="updateProperty({{ $selectedIndex }}, 'required', $event.target.checked)" @checked(!empty($questions[$selectedIndex]['properties']['required'])) type="checkbox"
                                               id="required-checkbox-{{ $selectedIndex }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="required-checkbox-{{ $selectedIndex }}" class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Champ obligatoire</label>
                                    </div>

                                    @if($questions[$selectedIndex]['type'] === 'textarea')
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de lignes</label>
                                            <input wire:input="updateProperty({{ $selectedIndex }}, 'rows', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['rows'] ?? '' }}" type="number" min="2" max="10"
                                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                        </div>
                                    @endif

                                    @if($questions[$selectedIndex]['type'] === 'number_input')
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min</label>
                                                <input wire:input="updateProperty({{ $selectedIndex }}, 'min', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['min'] ?? '' }}" type="number"
                                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="0">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max</label>
                                                <input wire:input="updateProperty({{ $selectedIndex }}, 'max', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['max'] ?? '' }}" type="number"
                                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="100">
                                            </div>
                                        </div>
                                    @endif

                                    @if($questions[$selectedIndex]['type'] === 'text_input')
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longueur maximale</label>
                                            <input wire:input="updateProperty({{ $selectedIndex }}, 'maxLength', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['maxLength'] ?? '' }}" type="number" min="1"
                                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Ex: 255">
                                        </div>
                                    @endif

                                    @if(in_array($questions[$selectedIndex]['type'], ['dropdown', 'radio']))
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Options <span class="text-gray-400 font-normal">(séparées par virgule)</span></label>
                                            <textarea wire:input="updateProperty({{ $selectedIndex }}, 'options', $event.target.value)" rows="3"
                                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                      placeholder="Option 1, Option 2, Option 3">{{ $questions[$selectedIndex]['properties']['options'] ?? '' }}</textarea>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Séparez chaque option par une virgule</p>
                                        </div>
                                    @endif

                                    @if($questions[$selectedIndex]['type'] === 'file_upload')
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Types de fichiers acceptés</label>
                                            <input wire:input="updateProperty({{ $selectedIndex }}, 'accept', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['accept'] ?? '' }}" type="text"
                                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder=".pdf, .doc, image/*">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ex: .pdf, .doc, image/*</p>
                                        </div>
                                    @endif
                                @endif

                                <!-- Badge type -->
                                <div class="mt-4 p-3 {{ str_starts_with($questions[$selectedIndex]['type'], 'block_') ? 'bg-violet-50 dark:bg-violet-900/20' : 'bg-blue-50 dark:bg-blue-900/20' }} rounded-lg">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 {{ str_starts_with($questions[$selectedIndex]['type'], 'block_') ? 'text-violet-600 dark:text-violet-400' : 'text-blue-600 dark:text-blue-400' }} mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium {{ str_starts_with($questions[$selectedIndex]['type'], 'block_') ? 'text-violet-900 dark:text-violet-300' : 'text-blue-900 dark:text-blue-300' }}">
                                                Type : {{ ucfirst(str_replace(['block_', '_'], ['', ' '], $questions[$selectedIndex]['type'])) }}
                                            </p>
                                            <p class="text-xs {{ str_starts_with($questions[$selectedIndex]['type'], 'block_') ? 'text-violet-700 dark:text-violet-400' : 'text-blue-700 dark:text-blue-400' }} mt-1">
                                                Modifiez les propriétés pour mettre à jour le composant
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
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

        <style>
            .scrollbar-thin::-webkit-scrollbar { width: 6px; }
            .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
            .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
            .dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #4a5568; }
            .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
            .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #5a6c7d; }
        </style>
    </div>
    @endvolt
</x-layouts.app>