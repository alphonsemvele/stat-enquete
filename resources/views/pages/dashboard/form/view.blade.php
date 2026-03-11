<?php

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Form;

// URL : /dashboard/form/view?ref=FORM-2025-A3X9K2
name('form.view');
middleware(['auth', 'verified']);

new class extends Component {

    public $form          = null;
    public $questions     = [];
    public string $formColor     = '#3B82F6';
    public string $formReference = '';

    public function mount(): void
    {
        $ref = request()->string('ref')->toString();

        $this->form = Form::with(['questions' => fn($q) => $q->orderBy('order')])
            ->withCount('responses')
            ->where('user_id', auth()->id())
            ->where('reference', $ref)
            ->firstOrFail();

        $this->formColor     = $this->form->color ?? '#3B82F6';
        $this->formReference = $this->form->reference ?? '';

        $this->questions = $this->form->questions
            ->map(fn($q) => ['type' => $q->type, 'properties' => $q->properties ?? []])
            ->toArray();
    }
};
?>
<x-layouts.app title="Aperçu — {{ $form?->title }}">
    @volt
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">

        <!-- ══ Barre supérieure ══ -->
        <div class="sticky top-0 z-30 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="max-w-4xl mx-auto px-6 py-3 flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <a href="/dashboard" class="p-2 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-base font-semibold text-gray-900 dark:text-white">{{ $form->title }}</h1>
                            <span class="hidden sm:inline text-xs font-mono px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-400 rounded-md border border-gray-200 dark:border-gray-600 select-all">
                                {{ $formReference }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400">Aperçu · lecture seule</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-4 pr-4 border-r border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $form->responses_count }}</p>
                            <p class="text-xs text-gray-400">réponse{{ $form->responses_count !== 1 ? 's' : '' }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ count($questions) }}</p>
                            <p class="text-xs text-gray-400">champ{{ count($questions) !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>

                    @if($form->is_published)
                        @if($form->accepts_responses)
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Actif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Fermé
                            </span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Brouillon
                        </span>
                    @endif

                    <a href="/dashboard/form/edit?id={{ $form->id }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Modifier
                    </a>
                </div>
            </div>
        </div>

        <!-- ══ Corps ══ -->
        <div class="max-w-2xl mx-auto px-4 py-10">

            @if(count($questions) === 0)
                <div class="text-center py-20">
                    <div class="w-20 h-20 mx-auto mb-4 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center shadow-sm">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-500 dark:text-gray-400 mb-2">Formulaire vide</h3>
                    <p class="text-sm text-gray-400 mb-5">Aucun champ pour l'instant.</p>
                    <a href="/dashboard/form/edit?id={{ $form->id }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter des champs
                    </a>
                </div>
            @else

                {{-- En-tête --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                    <div class="h-2 transition-all" style="background-color: {{ $formColor }}"></div>
                    <div class="px-8 py-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $form->title }}</h2>
                        @if($form->description)
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $form->description }}</p>
                        @endif
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-4 text-xs text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Modifié le {{ $form->updated_at->translatedFormat('j M. Y') }}
                            </span>
                            <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded select-all">{{ $formReference }}</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                {{ $form->responses_count }} réponse{{ $form->responses_count !== 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Champs --}}
                <div class="space-y-4">
                    @foreach($questions as $index => $question)

                        @if($question['type'] === 'block_title')
                            @php $sz = match($question['properties']['size'] ?? 'h2') { 'h1'=>'text-3xl','h2'=>'text-2xl','h3'=>'text-xl',default=>'text-2xl' }; @endphp
                            <div class="py-2">
                                <h3 class="{{ $sz }} font-bold text-gray-900 dark:text-white">{{ $question['properties']['title'] ?? 'Titre' }}</h3>
                                @if($question['properties']['subtitle'] ?? '')
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $question['properties']['subtitle'] }}</p>
                                @endif
                            </div>

                        @else
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                                <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                    {{ $question['properties']['label'] ?? 'Sans titre' }}
                                    @if($question['properties']['required'] ?? false)<span class="text-red-500 ml-0.5">*</span>@endif
                                </label>

                                @if($question['type'] === 'text_input')      <input type="text"   disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50/50 text-sm" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                @elseif($question['type'] === 'textarea')     <textarea disabled rows="{{ $question['properties']['rows'] ?? 4 }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50/50 text-sm" placeholder="{{ $question['properties']['placeholder'] ?? '' }}"></textarea>
                                @elseif($question['type'] === 'number_input') <input type="number" disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50/50 text-sm" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                @elseif($question['type'] === 'email')        <input type="email"  disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50/50 text-sm" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                @elseif($question['type'] === 'phone')        <input type="tel"    disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50/50 text-sm" placeholder="{{ $question['properties']['placeholder'] ?? '' }}">
                                @elseif($question['type'] === 'dropdown')
                                    <select disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50/50 text-sm">
                                        <option>{{ $question['properties']['placeholder'] ?? 'Sélectionnez' }}</option>
                                        @foreach(explode(',', $question['properties']['options'] ?? '') as $opt)<option>{{ trim($opt) }}</option>@endforeach
                                    </select>
                                @elseif($question['type'] === 'checkbox')
                                    <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/30">
                                        <input type="checkbox" disabled class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Case à cocher</span>
                                    </div>
                                @elseif($question['type'] === 'radio')
                                    <div class="space-y-2">
                                        @foreach(explode(',', $question['properties']['options'] ?? 'Option 1') as $opt)
                                            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/30 cursor-not-allowed">
                                                <input type="radio" disabled name="vr_{{ $index }}" class="w-4 h-4 text-blue-600 border-gray-300">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ trim($opt) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($question['type'] === 'date_picker')  <input type="date" disabled class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 bg-gray-50/50 text-sm">
                                @elseif($question['type'] === 'file_upload')
                                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center bg-gray-50/50 dark:bg-gray-700/30">
                                        <svg class="mx-auto h-10 w-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Glissez un fichier ou cliquez</p>
                                        @if($question['properties']['accept'] ?? '')<p class="text-xs text-gray-400 mt-1">{{ $question['properties']['accept'] }}</p>@endif
                                    </div>
                                @endif

                                @if($question['properties']['required'] ?? false)
                                    <p class="mt-2 text-xs text-red-500 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        Champ obligatoire
                                    </p>
                                @endif
                            </div>
                        @endif

                    @endforeach
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <button disabled class="px-8 py-3 bg-blue-600 text-white rounded-xl font-medium text-sm opacity-50 cursor-not-allowed shadow-sm">
                        Soumettre
                    </button>
                    <p class="text-xs text-gray-400 italic">Aperçu uniquement — soumission désactivée</p>
                </div>

            @endif
        </div>
    </div>
    @endvolt
</x-layouts.app>