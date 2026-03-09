
<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;
use App\Models\Type;
use App\Models\Icone;

name('admin');
middleware(['auth', 'verified', RoleBasedRedirect::class]);

new class extends Component {
    public $componentName = '';
    public $type;
    public $componentType = '';
    public $componentIcon = '';
    public $successMessage = '';
    public $errorMessage = '';
    public $sidebarOpen = true;
    public $showIconPicker = false;

    public function mount()
    {
        $this->type = Type::where('status', 'success')->get();
    }

    public function selectIcon($iconSvg)
    {
        $this->componentIcon = $iconSvg;
        $this->showIconPicker = false;
    }

    public function toggleIconPicker()
    {
        $this->showIconPicker = !$this->showIconPicker;
    }

    public function addComponent()
    {
        $this->validate([
            'componentName' => 'required|string|max:255',
            'componentType' => 'required|exists:types,id',
            'componentIcon' => 'required|string',
        ]);

        // Check if component already exists
        foreach ($this->availableComponents as $component) {
            if (strtolower($component['name']) === strtolower($this->componentName)) {
                $this->errorMessage = 'This component already exists.';
                return;
            }
        }

        // Get the selected type's value
        $type = Type::findOrFail($this->componentType);

        // Add new component
        $newComponent = [
            'name' => $this->componentName,
            'type' => $type->value,
            'icon' => $this->componentIcon
        ];

        $this->availableComponents[] = $newComponent;

        $this->successMessage = 'Component added successfully!';
        $this->reset(['componentName', 'componentType', 'componentIcon']);
        $this->dispatch('reset-messages')->self()->delay(3);
    }

    public function deleteComponent($index)
    {
        if (isset($this->availableComponents[$index])) {
            unset($this->availableComponents[$index]);
            $this->availableComponents = array_values($this->availableComponents);
            $this->successMessage = 'Component deleted successfully!';
            $this->dispatch('reset-messages')->self()->delay(3);
        }
    }

    public function resetMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function toggleSidebar()
    {
        $this->sidebarOpen = !$this->sidebarOpen;
    }

    public function getAvailableIconsProperty()
    {
        try {
            return Icone::where('status', 'success')->get()->map(function ($icone) {
                return [
                    'name' => $icone->name,
                    'icon' => $icone->value,
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to load icons: ' . $e->getMessage();
            return [];
        }
    }

    public $availableComponents = [
        [
            'name' => 'Text Input',
            'type' => 'text',
            'icon' => '<svg class="w-8 h-8 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h18M3 19h18M3 12h18"></path></svg>'
        ],
        [
            'name' => 'Checkbox',
            'type' => 'checkbox',
            'icon' => '<svg class="w-8 h-8 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
        ]
    ];
};
?>

<x-layouts.admin title="Admin - Component Management">
    @volt
    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-800 font-['Roboto',sans-serif]">
        <!-- Sidebar -->

        <!-- Main Content -->
        <div class="flex-1 lg:ml-0 transition-all duration-300 ease-in-out">
            <div class="p-6 w-full">
                <!-- Mobile Menu Toggle Button -->
                <div class="lg:hidden mb-4">
                    <button wire:click="toggleSidebar"
                        class="p-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="max-w-7xl mx-auto">
                    <!-- Breadcrumb -->
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="/admin"
                                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                        </path>
                                    </svg>
                                    Accueil
                                </a>
                            </li>
                        </ol>
                    </nav>

                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Gestion des Composants</h1>

                    <!-- Success/Error Messages -->
                    @if($successMessage)
                        <div
                            class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-800 dark:border-green-600 dark:text-green-200 rounded-lg">
                            {{ $successMessage }}
                        </div>
                    @endif

                    @if($errorMessage)
                        <div
                            class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 dark:bg-red-800 dark:border-red-600 dark:text-red-200 rounded-lg">
                            {{ $errorMessage }}
                        </div>
                    @endif

                    <!-- Add Component Form -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Ajouter un Nouveau Composant</h2>
                        <form wire:submit="addComponent">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="componentName"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom du Composant</label>
                                    <input wire:model="componentName" type="text" id="componentName"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Ex: Champ Email" required>
                                </div>
                                <div>
                                    <label for="componentType"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type du Composant</label>
                                    <select wire:model="componentType" id="componentType"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                        <option value="">Sélectionner un type</option>
                                        @foreach($type as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Icône du Composant</label>
                                    <div class="relative">
                                        <!-- Icon Preview and Select Button -->
                                        <button type="button" wire:click="toggleIconPicker"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                            <div class="flex items-center space-x-2">
                                                @if($componentIcon)
                                                    <div class="text-gray-600 dark:text-gray-300">
                                                        {!! str_replace('class="w-8 h-8"', 'class="w-5 h-5"', $componentIcon) !!}
                                                    </div>
                                                    <span class="text-sm">Icône sélectionnée</span>
                                                @else
                                                    <span class="text-sm text-gray-500">Cliquer pour choisir une icône</span>
                                                @endif
                                            </div>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <!-- Icon Picker Modal -->
                                        @if($showIconPicker)
                                            <div class="absolute top-full left-0 right-0 z-50 mt-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-96 overflow-y-auto">
                                                <div class="p-4">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Choisir une icône</h3>
                                                        <button type="button" wire:click="toggleIconPicker"
                                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    @if(!empty($availableIcons))
                                                        <div class="grid grid-cols-4 gap-3">
                                                            @foreach($availableIcons as $icon)
                                                                <button type="button" 
                                                                    wire:click="selectIcon('{{ htmlentities($icon['icon']) }}')"
                                                                    class="p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-blue-50 hover:border-blue-300 dark:hover:bg-blue-900 dark:hover:border-blue-500 transition-colors group flex flex-col items-center space-y-1
                                                                    {{ $componentIcon === $icon['icon'] ? 'bg-blue-50 border-blue-300 dark:bg-blue-900 dark:border-blue-500' : '' }}">
                                                                    <div class="text-gray-600 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                                                        {!! $icon['icon'] !!}
                                                                    </div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 text-center leading-tight">
                                                                        {{ $icon['name'] }}
                                                                    </span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Aucune icône disponible.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 font-medium">
                                    Ajouter le Composant
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Available Components List -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg">
                        <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Composants Disponibles</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($availableComponents as $index => $component)
                                <div
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 bg-gray-50 dark:bg-gray-800">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                {!! $component['icon'] !!}
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                    {{ $component['name'] }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Type:
                                                    {{ $component['type'] }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="deleteComponent({{ $index }})"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-2 -m-2 rounded-full hover:bg-red-100 dark:hover:bg-red-900 transition-colors"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce composant ?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="mt-4 p-3 bg-white dark:bg-gray-900 rounded-md">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Ce composant peut être utilisé dans les formulaires pour capturer des données de
                                            type {{ strtolower($component['name']) }}.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Click outside to close icon picker -->
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('reset-messages', () => {
                    window.setTimeout(() => {
                        @this.resetMessages();
                    }, 3000);
                });

                // Close icon picker when clicking outside
                document.addEventListener('click', function(event) {
                    const iconPicker = document.querySelector('[wire\\:click="toggleIconPicker"]');
                    const iconPickerModal = document.querySelector('.absolute.z-50');
                    
                    if (iconPickerModal && !iconPicker.contains(event.target) && !iconPickerModal.contains(event.target)) {
                        @this.showIconPicker = false;
                    }
                });
            });
        </script>
    @endvolt
</x-layouts.admin>
