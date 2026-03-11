<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Form;
use App\Models\FormQuestion;

// URL : /dashboard/form/edit?id=7
name('form.edit');
middleware(['auth', 'verified']);

new class extends Component {

    public ?int    $formId          = null;
    public string  $formTitle       = 'Nouveau formulaire';
    public string  $formDescription = '';
    public string  $formColor       = '#3B82F6';
    public ?string $formReference   = null;
    public bool    $editingTitle    = false;

    public $questions         = [];
    public $selectedComponent = null;
    public $selectedIndex     = null;
    public $successMessage    = '';
    public $errorMessage      = '';

    public $colorPalette = [
        '#3B82F6','#6366F1','#8B5CF6','#EC4899',
        '#EF4444','#F97316','#EAB308','#22C55E',
        '#14B8A6','#06B6D4','#64748B','#1E293B',
    ];

    public $availableComponents = [
        ['type'=>'text_input',   'label'=>'Champ Texte',      'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>',  'properties'=>['label'=>'Nouveau champ texte','placeholder'=>'Entrez du texte','required'=>false,'maxLength'=>'']],
        ['type'=>'textarea',     'label'=>'Zone de Texte',    'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>', 'properties'=>['label'=>'Nouvelle zone de texte','placeholder'=>'Entrez votre texte ici','required'=>false,'rows'=>'4']],
        ['type'=>'number_input', 'label'=>'Nombre',           'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>', 'properties'=>['label'=>'Nouveau champ nombre','placeholder'=>'Entrez un nombre','required'=>false,'min'=>'','max'=>'']],
        ['type'=>'email',        'label'=>'Email',            'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>', 'properties'=>['label'=>'Adresse email','placeholder'=>'exemple@email.com','required'=>false]],
        ['type'=>'dropdown',     'label'=>'Liste Déroulante', 'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>', 'properties'=>['label'=>'Nouvelle liste','placeholder'=>'Sélectionnez une option','required'=>false,'options'=>'Option 1, Option 2, Option 3']],
        ['type'=>'checkbox',     'label'=>'Case à Cocher',    'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'properties'=>['label'=>'Nouvelle case à cocher','required'=>false]],
        ['type'=>'radio',        'label'=>'Bouton Radio',     'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'properties'=>['label'=>'Nouveau groupe radio','required'=>false,'options'=>'Option 1, Option 2, Option 3']],
        ['type'=>'date_picker',  'label'=>'Date',             'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', 'properties'=>['label'=>'Sélectionnez une date','required'=>false]],
        ['type'=>'file_upload',  'label'=>'Fichier',          'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>', 'properties'=>['label'=>'Télécharger un fichier','required'=>false,'accept'=>'']],
        ['type'=>'phone',        'label'=>'Téléphone',        'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>', 'properties'=>['label'=>'Numéro de téléphone','placeholder'=>'+237 6XX XXX XXX','required'=>false]],
        ['type'=>'block_title',  'label'=>'Titre',            'icon'=>'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>', 'properties'=>['title'=>'Nouveau titre','subtitle'=>'','size'=>'h2']],
    ];

    // ── Chargement depuis DB via query string ?id= ────────────────────────
    public function mount(): void
    {
        $id = request()->integer('id');
        if (!$id) return;

        $form = Form::with(['questions' => fn($q) => $q->orderBy('order')])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $this->formId          = $form->id;
        $this->formTitle       = $form->title;
        $this->formDescription = $form->description ?? '';
        $this->formColor       = $form->color ?? '#3B82F6';
        $this->formReference   = $form->reference ?? null;

        $this->questions = $form->questions
            ->map(fn($q) => ['type' => $q->type, 'properties' => $q->properties ?? []])
            ->toArray();
    }

    // ── Actions ───────────────────────────────────────────────────────────
    public function selectHeader(): void  { $this->selectedComponent = 'form_header'; $this->selectedIndex = null; }
    public function deselectAll(): void   { $this->selectedComponent = null; $this->selectedIndex = null; }
    public function startEditTitle(): void { $this->editingTitle = true; }
    public function stopEditTitle(): void  { $this->editingTitle = false; $this->formTitle = trim($this->formTitle) ?: 'Nouveau formulaire'; }

    public function addComponent(string $type): void
    {
        $component = collect($this->availableComponents)->firstWhere('type', $type);
        if (!$component) return;
        $this->questions[]       = ['type' => $type, 'properties' => $component['properties']];
        $this->selectedIndex     = count($this->questions) - 1;
        $this->selectedComponent = $type;
    }

    public function selectComponent(int $index): void
    {
        if (!isset($this->questions[$index])) return;
        $this->selectedIndex     = $index;
        $this->selectedComponent = $this->questions[$index]['type'];
    }

    public function deleteComponent(int $index): void
    {
        if (!isset($this->questions[$index])) return;
        array_splice($this->questions, $index, 1);
        if ($this->selectedIndex === $index)        { $this->selectedComponent = null; $this->selectedIndex = null; }
        elseif ($this->selectedIndex > $index)      { $this->selectedIndex--; }
    }

    public function updateProperty(int $index, string $key, $value): void
    {
        if (!isset($this->questions[$index])) return;
        $q = $this->questions;
        $q[$index]['properties'][$key] = $value;
        $this->questions = $q;
    }

    public function moveUp(int $index): void
    {
        if ($index <= 0 || !isset($this->questions[$index])) return;
        $q = $this->questions;
        [$q[$index-1], $q[$index]] = [$q[$index], $q[$index-1]];
        $this->questions = $q;
        if ($this->selectedIndex === $index) $this->selectedIndex = $index - 1;
        elseif ($this->selectedIndex === $index - 1) $this->selectedIndex = $index;
    }

    public function moveDown(int $index): void
    {
        if ($index >= count($this->questions) - 1 || !isset($this->questions[$index])) return;
        $q = $this->questions;
        [$q[$index], $q[$index+1]] = [$q[$index+1], $q[$index]];
        $this->questions = $q;
        if ($this->selectedIndex === $index) $this->selectedIndex = $index + 1;
        elseif ($this->selectedIndex === $index + 1) $this->selectedIndex = $index;
    }

    public function duplicateComponent(int $index): void
    {
        if (!isset($this->questions[$index])) return;
        $this->questions[] = $this->questions[$index];
    }

    public function saveForm(): void
    {
        $this->validate([
            'formTitle'       => 'required|string|max:255',
            'formDescription' => 'nullable|string|max:1000',
            'formColor'       => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'questions'       => 'array',
        ]);

        try {
            if ($this->formId) {
                $form = Form::where('user_id', auth()->id())->findOrFail($this->formId);
                $form->update([
                    'title'       => trim($this->formTitle),
                    'description' => trim($this->formDescription) ?: null,
                    'color'       => $this->formColor,
                ]);
            } else {
                $form = Form::create([
                    'user_id'     => auth()->id(),
                    'title'       => trim($this->formTitle),
                    'description' => trim($this->formDescription) ?: null,
                    'color'       => $this->formColor,
                ]);
                $this->formId        = $form->id;
                $this->formReference = $form->reference ?? null;
            }

            $form->questions()->delete();
            foreach ($this->questions as $order => $q) {
                FormQuestion::create([
                    'form_id'    => $form->id,
                    'type'       => $q['type'],
                    'properties' => $q['properties'] ?? [],
                    'order'      => $order,
                ]);
            }

            $this->successMessage = $this->formId ? 'Formulaire mis à jour !' : 'Formulaire enregistré !';
            $this->errorMessage   = '';

        } catch (\Throwable $e) {
            $this->errorMessage   = 'Erreur : ' . $e->getMessage();
            $this->successMessage = '';
        }
    }

    public function publishForm(): void
    {
        try {
            $this->saveForm();
            if ($this->formId) {
                Form::where('user_id', auth()->id())->findOrFail($this->formId)->update(['is_published' => true]);
                $this->successMessage = 'Formulaire publié !';
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Erreur : ' . $e->getMessage();
        }
    }
};
?>
<x-layouts.app title="{{ $formId ? 'Modifier — '.$formTitle : 'Nouveau formulaire' }}">
    @volt
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-8">

        <!-- ══ HEADER ══ -->
        <div class="max-w-7xl mx-auto mb-8">
            <div class="flex items-center justify-between gap-4">

                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <a href="/dashboard" class="shrink-0 p-2 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-800 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>

                    @if($editingTitle)
                        <input wire:model.live="formTitle" wire:blur="stopEditTitle" wire:keydown.enter="stopEditTitle"
                               type="text" autofocus
                               class="text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-blue-400 outline-none focus:ring-0 flex-1 min-w-0 pb-0.5">
                    @else
                        <h1 wire:click="startEditTitle" title="Cliquer pour modifier"
                            class="text-2xl font-bold text-gray-900 dark:text-white cursor-text truncate hover:text-blue-600 dark:hover:text-blue-400 transition-colors group flex items-center gap-2">
                            {{ $formTitle }}
                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-40 transition-opacity shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.586-6.586a2 2 0 112.828 2.828L11.828 13.828A2 2 0 0111 14H9v-2a2 2 0 01.586-1.414z"/></svg>
                        </h1>
                    @endif

                    @if($formReference)
                        <span class="shrink-0 text-xs font-mono px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 rounded-full border border-gray-200 dark:border-gray-600 flex items-center gap-1.5 select-all">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $formColor }}"></span>
                            {{ $formReference }}
                        </span>
                    @else
                        <span class="shrink-0 text-xs font-medium px-2.5 py-0.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full">
                            Non enregistré
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    @if($formReference)
                    <a href="/dashboard/form/view?ref={{ $formReference }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700 hover:border-blue-300 hover:text-blue-600 transition-all text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Aperçu
                    </a>
                    @endif

                    <button wire:click="saveForm" wire:loading.attr="disabled" wire:target="saveForm"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700 hover:border-blue-300 hover:text-blue-600 disabled:opacity-60 transition-all text-sm font-medium">
                        <svg wire:loading wire:target="saveForm" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        <svg wire:loading.remove wire:target="saveForm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span wire:loading wire:target="saveForm">Sauvegarde…</span>
                        <span wire:loading.remove wire:target="saveForm">Enregistrer</span>
                    </button>

                    <button wire:click="publishForm" wire:loading.attr="disabled" wire:target="publishForm"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 disabled:opacity-60 transition-all text-sm font-medium">
                        <svg wire:loading wire:target="publishForm" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        <svg wire:loading.remove wire:target="publishForm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span wire:loading.remove wire:target="publishForm">Publier</span>
                        <span wire:loading wire:target="publishForm">Publication…</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ Flash ══ -->
        @if($successMessage)
            <div class="max-w-7xl mx-auto mb-6" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl flex items-center gap-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ $successMessage }}
                </div>
            </div>
        @endif
        @if($errorMessage)
            <div class="max-w-7xl mx-auto mb-6" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl flex items-center gap-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ $errorMessage }}
                </div>
            </div>
        @endif

        <!-- ══ GRID 5 COL ══ -->
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-5 gap-6">

                <!-- ── Sidebar gauche ── -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 sticky top-8">
                        <h2 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Composants</h2>
                        <div class="space-y-1.5 max-h-[75vh] overflow-y-auto">
                            @foreach($availableComponents as $component)
                                <button wire:click="addComponent('{{ $component['type'] }}')"
                                    class="w-full flex items-center p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 border-2 border-transparent hover:border-blue-200 dark:hover:border-blue-800 transition-all group">
                                    <div class="w-8 h-8 flex items-center justify-center bg-white dark:bg-gray-800 rounded-lg shadow-sm mr-2.5 shrink-0">
                                        <div class="w-4 h-4 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 transition-colors">{!! $component['icon'] !!}</div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors text-left">{{ $component['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ── Centre ── -->
                <div class="col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden min-h-[600px]">

                        {{-- En-tête cliquable --}}
                        <div wire:click="selectHeader" class="cursor-pointer group relative transition-all {{ $selectedComponent === 'form_header' ? 'ring-2 ring-inset ring-blue-400' : 'hover:ring-1 hover:ring-inset hover:ring-gray-300' }}">
                            <div class="h-4 transition-all" style="background-color: {{ $formColor }}"></div>
                            <div class="px-8 pt-6 pb-5 border-b border-gray-100 dark:border-gray-700/80">
                                <input wire:click.stop wire:input="$set('formTitle', $event.target.value)" value="{{ $formTitle }}" type="text" placeholder="Titre du formulaire…"
                                       class="w-full text-2xl font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-transparent hover:border-gray-200 focus:border-blue-400 outline-none focus:ring-0 transition-colors pb-0.5 placeholder-gray-300 mb-2">
                                <input wire:click.stop wire:input="$set('formDescription', $event.target.value)" value="{{ $formDescription }}" type="text" placeholder="Description (optionnelle)…"
                                       class="w-full text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 border-b-2 border-transparent hover:border-gray-200 focus:border-blue-400 outline-none focus:ring-0 transition-colors placeholder-gray-300">
                                <div class="absolute top-5 right-4 opacity-0 group-hover:opacity-100 transition-opacity text-xs text-gray-400 bg-white/80 dark:bg-gray-800/80 px-2 py-1 rounded-full shadow-sm">
                                    En-tête & couleur
                                </div>
                            </div>
                        </div>

                        {{-- Corps --}}
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-5">
                                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Champs</h2>
                                <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ count($questions) }}</span>
                            </div>

                            <div class="space-y-4">
                                @forelse($questions as $index => $question)

                                    @if($question['type'] === 'block_title')
                                        @php $sz = match($question['properties']['size'] ?? 'h2') { 'h1'=>'text-3xl','h2'=>'text-2xl','h3'=>'text-xl',default=>'text-2xl' }; @endphp
                                        <div wire:click="selectComponent({{ $index }})" wire:key="q-{{ $index }}"
                                             class="group relative rounded-xl border-2 transition-all cursor-pointer px-6 py-5 {{ $selectedIndex === $index ? 'border-violet-400 shadow-md bg-violet-50/30 dark:bg-violet-900/10' : 'border-gray-200 dark:border-gray-700 hover:border-violet-300' }}">
                                            <span class="absolute top-2 left-3 text-xs font-mono text-violet-400 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">{{ strtoupper($question['properties']['size'] ?? 'H2') }}</span>
                                            <input wire:click.stop wire:input="updateProperty({{ $index }}, 'title', $event.target.value)" value="{{ $question['properties']['title'] ?? '' }}" type="text" placeholder="Titre…"
                                                   class="w-full {{ $sz }} font-bold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-transparent hover:border-gray-200 focus:border-violet-400 outline-none focus:ring-0 transition-colors pb-0.5 cursor-text placeholder-gray-300">
                                            <input wire:click.stop wire:input="updateProperty({{ $index }}, 'subtitle', $event.target.value)" value="{{ $question['properties']['subtitle'] ?? '' }}" type="text" placeholder="Sous-titre…"
                                                   class="w-full mt-1 text-sm text-gray-500 dark:text-gray-400 bg-transparent border-0 outline-none focus:ring-0 cursor-text placeholder-gray-300">
                                            @include('_form_actions', ['index' => $index, 'total' => count($questions)])
                                        </div>

                                    @else
                                        <div wire:click="selectComponent({{ $index }})" wire:key="q-{{ $index }}"
                                             class="group relative p-5 rounded-xl border-2 transition-all cursor-pointer {{ $selectedIndex === $index ? 'border-blue-400 dark:border-blue-600 bg-blue-50/50 shadow-md' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 bg-gray-50/30' }}">
                                            <div class="mb-3">
                                                <div class="flex items-center mb-2 gap-1">
                                                    <input wire:click.stop wire:input="updateProperty({{ $index }}, 'label', $event.target.value)" value="{{ $question['properties']['label'] ?? '' }}" type="text" placeholder="Libellé…"
                                                           class="flex-1 text-sm font-semibold text-gray-900 dark:text-white bg-transparent border-0 border-b-2 border-transparent hover:border-gray-200 focus:border-blue-400 outline-none focus:ring-0 transition-colors pb-0.5 cursor-text placeholder-gray-300">
                                                    @if($question['properties']['required'] ?? false)<span class="text-red-500 font-bold">*</span>@endif
                                                </div>
                                                @if($question['type']==='text_input')    <input type="text"   disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50 text-sm" placeholder="{{ $question['properties']['placeholder']??'' }}">
                                                @elseif($question['type']==='textarea')  <textarea disabled rows="{{ $question['properties']['rows']??4 }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50 text-sm" placeholder="{{ $question['properties']['placeholder']??'' }}"></textarea>
                                                @elseif($question['type']==='number_input') <input type="number" disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50 text-sm" placeholder="{{ $question['properties']['placeholder']??'' }}">
                                                @elseif($question['type']==='email')     <input type="email"  disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50 text-sm" placeholder="{{ $question['properties']['placeholder']??'' }}">
                                                @elseif($question['type']==='phone')     <input type="tel"    disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50 text-sm" placeholder="{{ $question['properties']['placeholder']??'' }}">
                                                @elseif($question['type']==='dropdown')  <select disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50 text-sm"><option>{{ $question['properties']['placeholder']??'Sélectionnez' }}</option></select>
                                                @elseif($question['type']==='checkbox')  <div class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 bg-gray-50/50"><input type="checkbox" disabled class="w-4 h-4 rounded"><span class="text-sm text-gray-500">Case à cocher</span></div>
                                                @elseif($question['type']==='radio')     <div class="space-y-2">@foreach(explode(',',$question['properties']['options']??'Option 1') as $opt)<div class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 bg-gray-50/50"><input type="radio" disabled class="w-4 h-4"><span class="text-sm text-gray-500">{{ trim($opt) }}</span></div>@endforeach</div>
                                                @elseif($question['type']==='date_picker') <input type="date" disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50 text-sm">
                                                @elseif($question['type']==='file_upload') <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center bg-gray-50/50"><svg class="mx-auto h-8 w-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg><p class="text-xs text-gray-400">Glissez ou cliquez</p></div>
                                                @endif
                                            </div>
                                            {{-- Boutons action --}}
                                            <div class="absolute top-3 right-3 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click.stop="moveUp({{ $index }})" @if($index===0) disabled @endif class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm text-gray-500 hover:text-gray-700 transition disabled:opacity-30"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg></button>
                                                <button wire:click.stop="moveDown({{ $index }})" @if($index===count($questions)-1) disabled @endif class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm text-gray-500 hover:text-gray-700 transition disabled:opacity-30"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></button>
                                                <div class="w-px h-4 bg-gray-200 dark:bg-gray-600"></div>
                                                <button wire:click.stop="selectComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm text-gray-500 hover:text-violet-600 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></button>
                                                <button wire:click.stop="duplicateComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm text-gray-500 hover:text-blue-600 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></button>
                                                <button wire:click.stop="deleteComponent({{ $index }})" class="p-1.5 bg-white dark:bg-gray-800 rounded-lg shadow-sm text-gray-500 hover:text-red-600 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                            </div>
                                        </div>
                                    @endif

                                @empty
                                    <div class="text-center py-20">
                                        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background-color: {{ $formColor }}20">
                                            <svg class="w-8 h-8" style="color: {{ $formColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </div>
                                        <p class="text-sm text-gray-400">Ajoutez des composants depuis la barre gauche</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Sidebar droite ── -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 sticky top-8">

                        @if($selectedComponent === 'form_header')
                            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">En-tête du formulaire</h2>
                            <div class="space-y-5 max-h-[80vh] overflow-y-auto pr-1">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Titre</label>
                                    <input wire:model.live="formTitle" type="text" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Description</label>
                                    <textarea wire:model.live="formDescription" rows="3" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none" placeholder="Description optionnelle…"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Couleur</label>
                                    <div class="h-10 rounded-xl mb-3 shadow-inner transition-all" style="background-color: {{ $formColor }}"></div>
                                    <div class="grid grid-cols-6 gap-2 mb-3">
                                        @foreach($colorPalette as $color)
                                            <button wire:click="$set('formColor', '{{ $color }}')"
                                                    class="w-full aspect-square rounded-lg shadow-sm transition-all hover:scale-110 {{ $formColor === $color ? 'ring-2 ring-offset-2 ring-gray-500 dark:ring-offset-gray-800 scale-110' : '' }}"
                                                    style="background-color: {{ $color }}"></button>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model.live="formColor" class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer p-0.5 bg-transparent shrink-0">
                                        <input type="text" wire:model.live="formColor" maxlength="7" class="flex-1 px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all uppercase" placeholder="#3B82F6">
                                    </div>
                                </div>
                                @if($formReference)
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Référence</label>
                                    <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <code class="text-sm font-mono text-gray-700 dark:text-gray-300 flex-1 select-all">{{ $formReference }}</code>
                                        <button x-data @click="navigator.clipboard.writeText('{{ $formReference }}'); $el.textContent='✓'; setTimeout(()=>$el.textContent='Copier',2000)"
                                                class="text-xs text-gray-400 hover:text-gray-600 transition-colors">Copier</button>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1.5">Identifiant unique — non modifiable</p>
                                </div>
                                @endif
                            </div>

                        @elseif($selectedComponent && $selectedIndex !== null && isset($questions[$selectedIndex]))
                            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-5">Propriétés</h2>
                            <div class="space-y-4 max-h-[75vh] overflow-y-auto pr-1" wire:key="props-{{ $selectedIndex }}">
                                @if($questions[$selectedIndex]['type'] === 'block_title')
                                    <div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Titre</label><input wire:input="updateProperty({{ $selectedIndex }}, 'title', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['title']??'' }}" type="text" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"></div>
                                    <div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Sous-titre</label><input wire:input="updateProperty({{ $selectedIndex }}, 'subtitle', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['subtitle']??'' }}" type="text" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all"></div>
                                    <div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Taille</label><div class="grid grid-cols-3 gap-2">@foreach(['h1'=>'H1','h2'=>'H2','h3'=>'H3'] as $val=>$lab)<button wire:click="updateProperty({{ $selectedIndex }}, 'size', '{{ $val }}')" class="py-2 px-3 rounded-lg border-2 text-sm font-medium transition-all {{ ($questions[$selectedIndex]['properties']['size']??'h2')===$val ? 'border-violet-500 bg-violet-50 text-violet-700' : 'border-gray-200 text-gray-600 hover:border-violet-300' }}">{{ $lab }}</button>@endforeach</div></div>
                                @else
                                    <div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Libellé</label><input wire:input="updateProperty({{ $selectedIndex }}, 'label', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['label']??'' }}" type="text" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>
                                    @if(in_array($questions[$selectedIndex]['type'],['text_input','textarea','number_input','email','phone','dropdown']))<div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Placeholder</label><input wire:input="updateProperty({{ $selectedIndex }}, 'placeholder', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['placeholder']??'' }}" type="text" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>@endif
                                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"><input wire:change="updateProperty({{ $selectedIndex }}, 'required', $event.target.checked)" @checked(!empty($questions[$selectedIndex]['properties']['required'])) type="checkbox" id="req-{{ $selectedIndex }}" class="w-4 h-4 text-blue-600 border-gray-300 rounded"><label for="req-{{ $selectedIndex }}" class="ml-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">Champ obligatoire</label></div>
                                    @if($questions[$selectedIndex]['type']==='textarea')<div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Lignes</label><input wire:input="updateProperty({{ $selectedIndex }}, 'rows', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['rows']??'' }}" type="number" min="2" max="10" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>@endif
                                    @if($questions[$selectedIndex]['type']==='number_input')<div class="grid grid-cols-2 gap-3"><div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Min</label><input wire:input="updateProperty({{ $selectedIndex }}, 'min', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['min']??'' }}" type="number" placeholder="0" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div><div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Max</label><input wire:input="updateProperty({{ $selectedIndex }}, 'max', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['max']??'' }}" type="number" placeholder="100" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div></div>@endif
                                    @if($questions[$selectedIndex]['type']==='text_input')<div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Longueur max</label><input wire:input="updateProperty({{ $selectedIndex }}, 'maxLength', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['maxLength']??'' }}" type="number" min="1" placeholder="255" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>@endif
                                    @if(in_array($questions[$selectedIndex]['type'],['dropdown','radio']))<div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Options</label><textarea wire:input="updateProperty({{ $selectedIndex }}, 'options', $event.target.value)" rows="4" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Option 1, Option 2">{{ $questions[$selectedIndex]['properties']['options']??'' }}</textarea></div>@endif
                                    @if($questions[$selectedIndex]['type']==='file_upload')<div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Types acceptés</label><input wire:input="updateProperty({{ $selectedIndex }}, 'accept', $event.target.value)" value="{{ $questions[$selectedIndex]['properties']['accept']??'' }}" type="text" placeholder=".pdf, image/*" class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></div>@endif
                                @endif
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-700"><p class="text-xs text-gray-400">Type : <span class="font-mono text-gray-500 dark:text-gray-300">{{ $questions[$selectedIndex]['type'] }}</span></p></div>
                            </div>

                        @else
                            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Propriétés</h2>
                            <div class="text-center py-10">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center" style="background-color: {{ $formColor }}20">
                                    <svg class="w-6 h-6" style="color: {{ $formColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
                                </div>
                                <p class="text-xs text-gray-400 mb-4">Cliquez sur un champ ou l'en-tête pour éditer</p>
                                <button wire:click="selectHeader" class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-2 rounded-lg border-2 transition-all" style="border-color: {{ $formColor }}40; color: {{ $formColor }}">
                                    Modifier l'en-tête
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endvolt
</x-layouts.app>