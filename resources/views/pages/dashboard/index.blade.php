<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;
use App\Models\Form;

name('dashboard');
middleware(['auth', 'verified', RoleBasedRedirect::class]);

new class extends Component {
    public $forms = [];

    public function mount(): void
    {
        $this->loadForms();
    }

    public function loadForms(): void
    {
        $this->forms = Form::where('user_id', auth()->id())
            ->withCount('responses')
            ->latest()
            ->get()
            ->map(fn($f) => [
                'id'        => $f->id,
                'reference' => $f->reference ?? '',
                'name'      => $f->title,
                'date'      => $f->updated_at->translatedFormat('j M. Y'),
                'status'    => $f->is_published
                                ? ($f->accepts_responses ? 'Actif' : 'Fermé')
                                : 'Brouillon',
                'replies'   => $f->responses_count,
                'reference' => $f->reference,
                'bg'        => $this->colorFor($f->id),
                'icon'      => $this->iconFor($f->id),
            ])
            ->toArray();
    }

    public function deleteForm(int $id): void
    {
        $form = Form::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $form->delete();
        $this->loadForms();
        session()->flash('success', 'Formulaire supprimé.');
    }

    private function colorFor(int $id): string
    {
        $colors = ['bg-blue-500','bg-purple-500','bg-teal-500','bg-orange-400','bg-rose-500','bg-indigo-500','bg-green-500','bg-yellow-500'];
        return $colors[$id % count($colors)];
    }

    private function iconFor(int $id): string
    {
        $icons = ['📋','✏️','📝','💬','📊','🗂️','📌','📎'];
        return $icons[$id % count($icons)];
    }
};
?>
<x-layouts.app>
    @volt
    <div class="w-full bg-gray-100 dark:bg-gray-800 min-h-screen p-6">
        <section class="max-w-7xl mx-auto">

            {{-- Flash --}}
            @if(session('success'))
                <div x-data x-init="setTimeout(() => $el.remove(), 3000)"
                     class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-5 py-3 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Bloc Nouveau Formulaire --}}
            <div class="mb-10">
                <div class="flex flex-col justify-center items-center bg-white border border-gray-200 rounded-lg shadow-sm w-52 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-800 transition-colors">
                    <div class="items-center p-3">
                        <img class="object-cover w-full rounded-t-lg md:h-auto md:w-48 md:rounded-none md:rounded-s-lg"
                            src="{{ asset('images/home.webp') }}" alt="">
                    </div>
                    <div class="flex flex-col justify-center items-center p-4 leading-normal mx-auto">
                        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400 text-center text-xs">
                            Créez un nouveau formulaire vierge
                        </p>
                        <a href="/dashboard/form"
                            class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors">
                            Nouveau formulaire
                        </a>
                    </div>
                </div>
            </div>

            {{-- Section Historique --}}
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200">Formulaires récents</h2>
                <span class="text-sm text-gray-400 dark:text-gray-500">
                    {{ count($forms) }} formulaire{{ count($forms) !== 1 ? 's' : '' }}
                </span>
            </div>

            @if(count($forms) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach($forms as $form)
                    <div class="group bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer">

                        {{-- Bannière couleur --}}
                        <div class="h-24 {{ $form['bg'] }} flex items-center justify-center relative">
                            <span class="text-4xl">{{ $form['icon'] }}</span>

                            {{-- Badge statut --}}
                            <div class="absolute top-2 right-2">
                                @if($form['status'] === 'Actif')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-white text-green-600">● Actif</span>
                                @elseif($form['status'] === 'Fermé')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-white text-red-500">● Fermé</span>
                                @else
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-white text-yellow-500">● Brouillon</span>
                                @endif
                            </div>

                            {{-- Menu contextuel (⋮) --}}
                            <div class="absolute top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity" x-data="{ open: false }">
                                <button @click.stop="open = !open"
                                        class="w-7 h-7 flex items-center justify-center rounded-full bg-white/80 hover:bg-white text-gray-600 shadow-sm transition">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition
                                     class="absolute left-0 mt-1 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 py-1">
                                    <a href="/dashboard/form/edit?id={{ $form['id'] }}"
                                       class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Modifier
                                    </a>
                                    <button wire:click="deleteForm({{ $form['id'] }})"
                                            onclick="return confirm('Supprimer ce formulaire ?')"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Infos --}}
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-white truncate mb-1">
                                {{ $form['name'] }}
                            </h3>
                            <p class="text-xs font-mono text-gray-400 dark:text-gray-500 mb-2 select-all">{{ $form['reference'] }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                                Modifié le {{ $form['date'] }}
                            </p>

                            <div class="flex items-center justify-between">
                                {{-- Avatar utilisateur connecté --}}
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ explode(' ', auth()->user()->name)[0] }}
                                    </span>
                                </div>

                                {{-- Réponses --}}
                                <div class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    {{ $form['replies'] }} réponse{{ $form['replies'] !== 1 ? 's' : '' }}
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="px-4 pb-4 flex gap-2 border-t border-gray-100 dark:border-gray-700 pt-3">
                            <a href="/dashboard/form/view?ref={{ $form['reference'] }}"
                                class="flex-1 text-center text-xs py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                Voir
                            </a>
                            <a href="/dashboard/form/edit?id={{ $form['id'] }}"
                                class="flex-1 text-center text-xs py-1.5 rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-800 transition">
                                Modifier
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

            @else
                {{-- État vide --}}
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 bg-white dark:bg-gray-900 rounded-full flex items-center justify-center shadow-sm mb-5">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-600 dark:text-gray-300 mb-1">Aucun formulaire pour l'instant</h3>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-5">Créez votre premier formulaire pour commencer à collecter des réponses.</p>
                    <a href="/dashboard/form"
                       class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 transition-colors">
                        Créer un formulaire
                    </a>
                </div>
            @endif

        </section>
    </div>
    @endvolt
</x-layouts.app>