<?php

namespace App\Http\Livewire;

use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;
use App\Models\Type;
use App\Models\Icone;
use App\Models\Components;
use App\Models\Property;
use App\Models\PropertyItem;
use Illuminate\Support\Facades\Log;

name('admin');
middleware(['auth', 'verified', RoleBasedRedirect::class]);

new class extends Component {
    public $componentName = '';
    public $type;
    public $componentType;
    public $componentIcon = '';
    public $componentStatus = 'pending';
    public $componentProperty = '';
    public $componentPropertyItem = '';
    public $successMessage = '';
    public $errorMessage = '';
    public $sidebarOpen = true;
    public $showIconPicker = false;
    public $components;
    public $availableIcons = [];
    public $availableProperties = [];
    public $availablePropertyItems = [];
    public $editingComponentId = null;
    public $isEditing = false;
    public $showCategoryModal = false;

    public function mount()
    {
        $this->type = Type::where('status', 'success')->get();
        $this->availableIcons = Icone::where('status', 'success')->get()->map(function ($icone) {
            return [
                'name' => $icone->name,
                'icon' => $icone->value,
                'id' => $icone->id,
            ];
        })->toArray();

        $this->components = $this->getComponentsProperty();
    }

    public function selectIcon($id)
    {
        $icon = Icone::where('id', $id)->first();
        $this->componentIcon = $icon->value;
        $this->showIconPicker = false;
    }

    public function updatedComponentType($value)
    {
        // Reset dependent fields
        $this->componentProperty = '';
        $this->componentPropertyItem = '';
        $this->availablePropertyItems = [];
        $this->errorMessage = '';

        // Validate type_id
        if (empty($value) || !Type::find($value)) {
            $this->errorMessage = 'Type de composant invalide.';
            Log::warning('Invalid type_id in updatedComponentType', ['type_id' => $value]);
            $this->availableProperties = [];
            return;
        }

        // Fetch only static properties for selected type
        $properties = Property::where('type_id', $value)
            ->where('status', 'success')
            ->where('category', 'static')
            ->get();

        if ($properties->isEmpty()) {
            $this->errorMessage = 'Aucune propriété statique disponible pour ce type.';
            Log::info('No static properties found for type_id', ['type_id' => $value]);
        }

        $this->availableProperties = $properties->map(function ($property) {
            return [
                'id' => $property->id,
                'name' => $property->name,
            ];
        })->toArray();
    }

    public function changeProperty()
    {
        if (!$this->componentType) {
            $this->errorMessage = 'Veuillez sélectionner un type avant de filtrer les propriétés.';
            return;
        }

        // Already filtering static properties in updatedComponentType, so this can be a no-op or reset to static
        $properties = Property::where('type_id', $this->componentType)
            ->where('category', 'static')
            ->where('status', 'success')
            ->get();

        $this->availableProperties = $properties->map(function ($property) {
            return [
                'id' => $property->id,
                'name' => $property->name,
            ];
        })->toArray();
    }

    public function updatedComponentProperty($value)
    {
        // Reset items when property changes
        $this->componentPropertyItem = '';
        $this->availablePropertyItems = [];
        $this->errorMessage = '';

        if (!empty($value)) {
            // Validate property_id
            if (!Property::find($value)) {
                $this->errorMessage = 'Propriété invalide.';
                Log::warning('Invalid property_id in updatedComponentProperty', ['property_id' => $value]);
                return;
            }

            // Fetch items for selected property
            $items = PropertyItem::where('property_id', $value)
                ->where('status', 'success')
                ->get();

            if ($items->isEmpty()) {
                $this->errorMessage = 'Aucun élément disponible pour cette propriété.';
                Log::info('No property items found for property_id', ['property_id' => $value]);
            }

            $this->availablePropertyItems = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'value' => $item->value,
                ];
            })->toArray();
        }
    }

    public function toggleIconPicker()
    {
        $this->showIconPicker = !$this->showIconPicker;
    }

    public function toggleCategoryModal()
    {
        $this->showCategoryModal = !$this->showCategoryModal;
    }

    public function addComponent()
    {
        $this->validate([
            'componentName' => 'required|string|max:255',
            'componentType' => 'required|exists:types,id',
            'componentIcon' => 'required|string',
            'componentStatus' => 'required|in:pending,success',
            'componentProperty' => 'nullable|exists:properties,id',
            'componentPropertyItem' => 'nullable|exists:property_items,id',
        ]);

        // Check if component already exists
        $existingComponent = Components::where('name', $this->componentName)->first();
        if ($existingComponent) {
            $this->errorMessage = 'Ce composant existe déjà.';
            return;
        }

        // Get the selected type
        $type = Type::findOrFail($this->componentType);

        // Get the icon
        $icon = Icone::where('value', $this->componentIcon)->first();
        if (!$icon) {
            $this->errorMessage = 'Icône invalide.';
            return;
        }

        // Get all non-static properties for the selected type
        $nonStaticProperties = Property::where('type_id', $this->componentType)
            ->where('status', 'success')
            ->where('category', '!=', 'static')
            ->get();

        // Initialize properties structure
        $propertiesStructure = [];

        // Add non-static properties with all their items
        foreach ($nonStaticProperties as $property) {
            $items = PropertyItem::where('property_id', $property->id)
                ->where('status', 'success')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'value' => $item->value,
                        'status' => $item->status,
                    ];
                })->toArray();

            $propertiesStructure[] = [
                'id' => $property->id,
                'name' => $property->name,
                'category' => $property->category,
                'status' => $property->status,
                'items' => $items,
                'selected' => false,
                'selected_item' => null,
            ];
        }

        // Add selected static property with only the selected item (if any)
        if ($this->componentProperty) {
            $selectedProperty = Property::find($this->componentProperty);
            if ($selectedProperty && $selectedProperty->category === 'static') {
                $selectedItem = null;
                if ($this->componentPropertyItem) {
                    $item = PropertyItem::find($this->componentPropertyItem);
                    if ($item) {
                        $selectedItem = [
                            'id' => $item->id,
                            'value' => $item->value,
                            'status' => $item->status,
                        ];
                    }
                }

                $propertiesStructure[] = [
                    'id' => $selectedProperty->id,
                    'name' => $selectedProperty->name,
                    'category' => $selectedProperty->category,
                    'status' => $selectedProperty->status,
                    'items' => $selectedItem ? [$selectedItem] : [], // Only include the selected item
                    'selected' => true,
                    'selected_item' => $selectedItem,
                ];
            }
        }

        // Create new component
        Components::create([
            'name' => $this->componentName,
            'structure' => [
                'type' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'value' => $type->value,
                    'status' => $type->status,
                ],
                'icon' => [
                    'id' => $icon->id,
                    'name' => $icon->name,
                    'value' => $icon->value,
                    'status' => $icon->status,
                ],
                'properties' => $propertiesStructure,
            ],
            'status' => $this->componentStatus,
        ]);

        $this->components = $this->getComponentsProperty();
        $this->successMessage = 'Composant ajouté avec succès !';
        $this->resetForm();
        $this->dispatch('reset-messages');
    }

    public function editComponent($componentId)
    {
        $component = Components::findOrFail($componentId);
        $this->editingComponentId = $componentId;
        $this->isEditing = true;

        // Populate form fields
        $this->componentName = $component->name;
        $this->componentType = Type::where('name', $component->structure['type']['name'])->first()->id;
        $this->componentIcon = $component->structure['icon']['value'];
        $this->componentStatus = $component->status;

        // Trigger property and item updates
        $this->updatedComponentType($this->componentType);
        if (isset($component->structure['properties']) && is_array($component->structure['properties'])) {
            $selectedProperty = collect($component->structure['properties'])->firstWhere('selected', true);
            if ($selectedProperty && $selectedProperty['category'] === 'static') {
                $this->componentProperty = $selectedProperty['id'];
                $this->updatedComponentProperty($this->componentProperty);
                if (isset($selectedProperty['selected_item'])) {
                    $this->componentPropertyItem = $selectedProperty['selected_item']['id'];
                }
            }
        }
    }

    public function updateComponent()
    {
        $this->validate([
            'componentName' => 'required|string|max:255',
            'componentType' => 'required|exists:types,id',
            'componentIcon' => 'required|string',
            'componentStatus' => 'required|in:pending,success',
            'componentProperty' => 'nullable|exists:properties,id',
            'componentPropertyItem' => 'nullable|exists:property_items,id',
        ]);

        // Check if another component with the same name exists
        $existingComponent = Components::where('name', $this->componentName)
            ->where('id', '!=', $this->editingComponentId)
            ->first();
        if ($existingComponent) {
            $this->errorMessage = 'Ce nom de composant est déjà utilisé.';
            return;
        }

        // Get the selected type
        $type = Type::findOrFail($this->componentType);

        // Get the icon
        $icon = Icone::where('value', $this->componentIcon)->first();
        if (!$icon) {
            $this->errorMessage = 'Icône invalide.';
            return;
        }

        // Get all non-static properties for the selected type
        $nonStaticProperties = Property::where('type_id', $this->componentType)
            ->where('status', 'success')
            ->where('category', '!=', 'static')
            ->get();

        // Initialize properties structure
        $propertiesStructure = [];

        // Add non-static properties with all their items
        foreach ($nonStaticProperties as $property) {
            $items = PropertyItem::where('property_id', $property->id)
                ->where('status', 'success')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'value' => $item->value,
                        'status' => $item->status,
                    ];
                })->toArray();

            $propertiesStructure[] = [
                'id' => $property->id,
                'name' => $property->name,
                'category' => $property->category,
                'status' => $property->status,
                'items' => $items,
                'selected' => false,
                'selected_item' => null,
            ];
        }

        // Add selected static property with only the selected item (if any)
        if ($this->componentProperty) {
            $selectedProperty = Property::find($this->componentProperty);
            if ($selectedProperty && $selectedProperty->category === 'static') {
                $selectedItem = null;
                if ($this->componentPropertyItem) {
                    $item = PropertyItem::find($this->componentPropertyItem);
                    if ($item) {
                        $selectedItem = [
                            'id' => $item->id,
                            'value' => $item->value,
                            'status' => $item->status,
                        ];
                    }
                }

                $propertiesStructure[] = [
                    'id' => $selectedProperty->id,
                    'name' => $selectedProperty->name,
                    'category' => $selectedProperty->category,
                    'status' => $selectedProperty->status,
                    'items' => $selectedItem ? [$selectedItem] : [], // Only include the selected item
                    'selected' => true,
                    'selected_item' => $selectedItem,
                ];
            }
        }

        // Update the component
        $component = Components::findOrFail($this->editingComponentId);
        $component->update([
            'name' => $this->componentName,
            'structure' => [
                'type' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'value' => $type->value,
                    'status' => $type->status,
                ],
                'icon' => [
                    'id' => $icon->id,
                    'name' => $icon->name,
                    'value' => $icon->value,
                    'status' => $icon->status,
                ],
                'properties' => $propertiesStructure,
            ],
            'status' => $this->componentStatus,
        ]);

        $this->components = $this->getComponentsProperty();
        $this->successMessage = 'Composant mis à jour avec succès !';
        $this->resetForm();
        $this->dispatch('reset-messages');
    }

    public function deleteComponent($componentId)
    {
        $component = Components::find($componentId);
        if ($component) {
            $component->delete();
            $this->components = $this->getComponentsProperty();
            $this->successMessage = 'Composant supprimé avec succès !';
            $this->resetForm();
            $this->dispatch('reset-messages');
        }
    }

    public function resetForm()
    {
        $this->reset([
            'componentName',
            'componentType',
            'componentIcon',
            'componentStatus',
            'componentProperty',
            'componentPropertyItem',
            'editingComponentId',
            'isEditing',
            'showCategoryModal',
        ]);
        $this->availableProperties = [];
        $this->availablePropertyItems = [];
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

    public function getComponentsProperty()
    {
        return Components::whereIn('status', ['success', 'pending'])->get();
    }
};
?>
<x-layouts.admin title="Admin - Component Management">
    @volt
    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-800 font-['Roboto',sans-serif]">
        <!-- Main Content -->
        <div class="flex-1 lg:ml-0 ml-64 transition-all duration-300 ease-in-out">
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

                    <!-- Add/Edit Component Form -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg mb-8">
                        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
                            {{ $isEditing ? 'Modifier le Composant' : 'Ajouter un Nouveau Composant' }}
                        </h2>
                        <form wire:submit="{{ $isEditing ? 'updateComponent' : 'addComponent' }}">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                                    <select wire:model.live="componentType" id="componentType"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                        <option value="">Sélectionner un type</option>
                                        @foreach($type as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="componentProperty"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Propriété
                                        <button 
                                            wire:click="changeProperty"
                                            type="button"
                                            class="inline-block ml-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                            title="Filtrer les propriétés statiques"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1m-2 8h-4m-5 0H3m12 6h-4m-5 0H3"></path>
                                            </svg>
                                        </button>
                                        <button 
                                            wire:click="toggleCategoryModal"
                                            type="button"
                                            class="inline-block ml-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                            title="Category Information"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    </label>
                                    <select wire:model.live="componentProperty" id="componentProperty"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Sélectionner une propriété</option>
                                        @foreach($availableProperties as $property)
                                            <option value="{{ $property['id'] }}">{{ $property['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="componentPropertyItem"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Élément de Propriété</label>
                                    <select wire:model="componentPropertyItem" id="componentPropertyItem"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Sélectionner un élément</option>
                                        @foreach($availablePropertyItems as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['value'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="componentStatus"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut du Composant</label>
                                    <select wire:model="componentStatus" id="componentStatus"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                        <option value="">Sélectionner un statut</option>
                                        <option value="pending">En attente</option>
                                        <option value="success">Succès</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Icône du Composant</label>
                                    <div class="relative">
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
                                                    <div class="grid grid-cols-4 gap-3">
                                                        @foreach($availableIcons as $icon)
                                                            <button type="button" 
                                                                wire:click="selectIcon('{{ $icon['id'] }}')"
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
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 flex space-x-4">
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 font-medium">
                                    {{ $isEditing ? 'Mettre à jour le Composant' : 'Ajouter le Composant' }}
                                </button>
                                @if($isEditing)
                                    <button type="button" wire:click="resetForm"
                                        class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-200 font-medium">
                                        Annuler
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Available Components List -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg">
                        <h2 class="text-xl font-semibold mb-6 text-gray-900 dark:text-white">Composants Disponibles</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($components as $component)
                                <div
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 bg-gray-50 dark:bg-gray-800">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                {!! $component->structure['icon']['value'] !!}
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                    {{ $component->name }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Type:
                                                    {{ $component->structure['type']['name'] }} ({{ $component->structure['type']['value'] }})</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Icône:
                                                    {{ $component->structure['icon']['name'] }}</p>
                                                @if(isset($component->structure['properties']) && is_array($component->structure['properties']))
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">Propriétés (Static):</p>
                                                    <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-400">
                                                        @foreach($component->structure['properties'] as $property)
                                                            @if($property['category'] === 'static')
                                                                <li>
                                                                    {{ $property['name'] }}
                                                                    @if(!empty($property['items']))
                                                                        <ul class="list-circle pl-5">
                                                                            @foreach($property['items'] as $item)
                                                                                <li>{{ $item['value'] }}{{ $property['selected'] && isset($property['selected_item']) && $property['selected_item']['id'] == $item['id'] ? ' (Sélectionné)' : '' }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                    @if($property['selected'] && isset($property['selected_item']))
                                                                        <p class="text-xs text-blue-600 dark:text-blue-400">Sélectionné: {{ $property['selected_item']['value'] }}</p>
                                                                    @endif
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                @endif
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Statut:
                                                    {{ $component->status === 'pending' ? 'En attente' : 'Succès' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col space-y-2">
                                            <button wire:click="editComponent({{ $component->id }})"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 p-2 -m-2 rounded-full hover:bg-blue-100 dark:hover:bg-blue-900 transition-colors"
                                                title="Modifier le composant">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button wire:click="deleteComponent({{ $component->id }})"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-2 -m-2 rounded-full hover:bg-red-100 dark:hover:bg-red-900 transition-colors"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce composant ?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-4 p-3 bg-white dark:bg-gray-900 rounded-md">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Ce composant peut être utilisé dans les formulaires pour capturer des données de
                                            type {{ strtolower($component->structure['type']['name']) }}.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Category Info Modal -->
                    @if($showCategoryModal)
                    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="category-modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                            <!-- Background overlay -->
                            <div wire:click="toggleCategoryModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity cursor-pointer" aria-hidden="true"></div>

                            <!-- Modal panel -->
                            <div class="inline-block align-bottom bg-white dark:bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="category-modal-title">Category Information</h3>
                                        <button wire:click="toggleCategoryModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="mt-4">
                                        <ul class="list-disc pl-5 space-y-2 text-gray-700 dark:text-gray-300">
                                            <li><strong>Static:</strong> Imposed by the system (e.g., type)</li>
                                            <li><strong>Dynamic:</strong> Modifiable by the user (e.g., placeholder)</li>
                                            <li><strong>Multiple:</strong> Contains multiple values (e.g., class)</li>
                                            <li><strong>Unique:</strong> Unique in the form (e.g., id, name)</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button 
                                        wire:click="toggleCategoryModal" 
                                        type="button"
                                        class="w-full sm:w-auto px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 sm:ml-3 sm:text-sm"
                                    >
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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