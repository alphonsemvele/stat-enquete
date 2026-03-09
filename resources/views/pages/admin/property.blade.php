<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;
use App\Models\Property;
use App\Models\Type;
use App\Models\PropertyItem;
use Illuminate\Support\Collection;

name('admin.properties');
// middleware(['auth', 'verified', RoleBasedRedirect::class]);

new class extends Component {
    public $name = '';
    public $type_id = '';
    public $status = 'pending';
    public $category = 'static';
    public $properties;
    public $types;
    public $successMessage = '';
    public $errorMessage = '';
    public $sidebarOpen = false;
    public $editingPropertyId = null;
    public $editingName = '';
    public $editingTypeId = '';
    public $editingStatus = 'pending';
    public $editingCategory = 'static';
    public $showItemsModal = false;
    public $selectedPropertyId = null;
    public $propertyItems;
    public $newItemValue = '';
    public $newItemStatus = 'pending';
    public $editingItemId = null;
    public $editingItemValue = '';
    public $editingItemStatus = 'pending';
    public $showCategoryModal = false; // New property for category modal

    public function mount()
    {
        $this->resetMessages();
        $this->properties = Property::with('type')->get();
        $this->types = Type::all();
        $this->propertyItems = new Collection();
    }

    public function toggleCategoryModal()
    {
        $this->showCategoryModal = !$this->showCategoryModal;
    }

    public function addProperty()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:types,id',
            'status' => 'required|in:pending,success,failed',
            'category' => 'required|in:static,dynamic,multiple,unique',
        ]);

        try {
            Property::create([
                'name' => $this->name,
                'type_id' => $this->type_id,
                'status' => $this->status,
                'category' => $this->category,
            ]);
            $this->properties = Property::with('type')->get();
            $this->successMessage = 'Property added successfully!';
            $this->reset(['name', 'type_id', 'status', 'category']);
            $this->dispatch('reset-messages');
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to add property: ' . $e->getMessage();
        }
    }

    public function editProperty($id)
    {
        $property = Property::findOrFail($id);
        $this->editingPropertyId = $id;
        $this->editingName = $property->name;
        $this->editingTypeId = $property->type_id;
        $this->editingStatus = $property->status;
        $this->editingCategory = $property->category;
    }

    public function updateProperty()
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingTypeId' => 'required|exists:types,id',
            'editingStatus' => 'required|in:pending,success,failed',
            'editingCategory' => 'required|in:static,dynamic,multiple,unique',
        ]);

        try {
            $property = Property::findOrFail($this->editingPropertyId);
            $property->update([
                'name' => $this->editingName,
                'type_id' => $this->editingTypeId,
                'status' => $this->editingStatus,
                'category' => $this->editingCategory,
            ]);
            $this->properties = Property::with('type')->get();
            $this->successMessage = 'Property updated successfully!';
            $this->reset(['editingPropertyId', 'editingName', 'editingTypeId', 'editingStatus', 'editingCategory']);
            $this->dispatch('reset-messages');
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update property: ' . $e->getMessage();
        }
    }

    public function deleteProperty($id)
    {
        try {
            $property = Property::findOrFail($id);
            $property->delete();
            $this->properties = Property::with('type')->get();
            $this->successMessage = 'Property deleted successfully!';
            $this->dispatch('reset-messages');

            if ($this->editingPropertyId === $id) {
                $this->reset(['editingPropertyId', 'editingName', 'editingTypeId', 'editingStatus', 'editingCategory']);
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to delete property: ' . $e->getMessage();
        }
    }

    public function showPropertyItems($propertyId)
    {
        $this->selectedPropertyId = $propertyId;
        $this->propertyItems = PropertyItem::where('property_id', $propertyId)->get();
        $this->showItemsModal = true;
        $this->reset(['newItemValue', 'newItemStatus', 'editingItemId', 'editingItemValue', 'editingItemStatus']);
    }

    public function addPropertyItem()
    {
        $this->validate([
            'newItemValue' => 'required|string|max:255',
            'newItemStatus' => 'required|in:pending,success,failed',
        ]);

        try {
            PropertyItem::create([
                'value' => $this->newItemValue,
                'property_id' => $this->selectedPropertyId,
                'status' => $this->newItemStatus,
            ]);
            $this->propertyItems = PropertyItem::where('property_id', $this->selectedPropertyId)->get();
            $this->successMessage = 'Property item added successfully!';
            $this->reset(['newItemValue', 'newItemStatus']);
            $this->dispatch('reset-messages');
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to add property item: ' . $e->getMessage();
        }
    }

    public function editPropertyItem($itemId)
    {
        $item = PropertyItem::findOrFail($itemId);
        $this->editingItemId = $itemId;
        $this->editingItemValue = $item->value;
        $this->editingItemStatus = $item->status;
    }

    public function updatePropertyItem()
    {
        $this->validate([
            'editingItemValue' => 'required|string|max:255',
            'editingItemStatus' => 'required|in:pending,success,failed',
        ]);

        try {
            $item = PropertyItem::findOrFail($this->editingItemId);
            $item->update([
                'value' => $this->editingItemValue,
                'status' => $this->editingItemStatus,
            ]);
            $this->propertyItems = PropertyItem::where('property_id', $this->selectedPropertyId)->get();
            $this->successMessage = 'Property item updated successfully!';
            $this->reset(['editingItemId', 'editingItemValue', 'editingItemStatus']);
            $this->dispatch('reset-messages');
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update property item: ' . $e->getMessage();
        }
    }

    public function cancelItemEdit()
    {
        $this->reset(['editingItemId', 'editingItemValue', 'editingItemStatus']);
    }

    public function deletePropertyItem($itemId)
    {
        try {
            $item = PropertyItem::findOrFail($itemId);
            $item->delete();
            $this->propertyItems = PropertyItem::where('property_id', $this->selectedPropertyId)->get();
            $this->successMessage = 'Property item deleted successfully!';
            $this->dispatch('reset-messages');

            if ($this->editingItemId === $itemId) {
                $this->reset(['editingItemId', 'editingItemValue', 'editingItemStatus']);
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to delete property item: ' . $e->getMessage();
        }
    }

    public function closeItemsModal()
    {
        $this->showItemsModal = false;
        $this->selectedPropertyId = null;
        $this->propertyItems = new Collection();
        $this->reset(['newItemValue', 'newItemStatus', 'editingItemId', 'editingItemValue', 'editingItemStatus']);
        $this->dispatch('modal-closed');
    }

    public function cancelEdit()
    {
        $this->reset(['editingPropertyId', 'editingName', 'editingTypeId', 'editingStatus', 'editingCategory']);
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

    public function getPropertiesProperty()
    {
        return Property::with('type')->get();
    }
};
?>

<x-layouts.admin title="Admin - Property Management">
    @volt
    <div class="flex min-h-screen bg-gray-100 dark:bg-gray-800 font-['Roboto',sans-serif]">
        <!-- Sidebar -->

        <!-- Main Content -->
        <div class="flex-1 p-6 w-full">
            <!-- Mobile Menu Toggle Button -->
            <div class="md:hidden mb-4">
                <button wire:click="toggleSidebar" class="p-2 bg-gray-900 text-white rounded-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Property Management</h1>

                <!-- Success/Error Messages -->
                @if($successMessage)
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-800 dark:border-green-600 dark:text-green-200 rounded-md">
                        {{ $successMessage }}
                    </div>
                @endif

                @if($errorMessage)
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 dark:bg-red-800 dark:border-red-600 dark:text-red-200 rounded-md">
                        {{ $errorMessage }}
                    </div>
                @endif

                <!-- Add Property Form -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Add New Property</h2>
                    <form wire:submit="addProperty">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                                <input 
                                    wire:model="name" 
                                    type="text" 
                                    id="name" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., Color"
                                    required
                                >
                            </div>
                            <div>
                                <label for="type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                                <select 
                                    wire:model="type_id" 
                                    id="type_id" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required
                                >
                                    <option value="">Select a type</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                             <div class="relative">
                                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Category
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
                                <select 
                                    wire:model="category" 
                                    id="category" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required
                                >
                                    <option value="static">Static</option>
                                    <option value="dynamic">Dynamic</option>
                                    <option value="multiple">Multiple</option>
                                    <option value="unique">Unique</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                <select 
                                    wire:model="status" 
                                    id="status" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    required
                                >
                                    <option value="pending">Pending</option>
                                    <option value="success">Success</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                           
                        </div>
                        <div class="mt-6">
                            <button 
                                type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200"
                            >
                                Add Property
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Properties Table -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Available Properties</h2>
                    @if($properties->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">No properties available.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Name</th>
                                        <th scope="col" class="px-6 py-3">Type</th>
                                        <th scope="col" class="px-6 py-3">Category</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($properties as $property)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            @if($editingPropertyId === $property->id)
                                                <td class="px-6 py-4">
                                                    <input 
                                                        wire:model="editingName" 
                                                        type="text" 
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                                                        placeholder="Enter name"
                                                    >
                                                </td>
                                                <td class="px-6 py-4">
                                                    <select 
                                                        wire:model="editingTypeId" 
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                                                    >
                                                        <option value="">Select a type</option>
                                                        @foreach($types as $type)
                                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="relative">
                                                        <label for="editingCategory" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                            Category
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
                                                        <select 
                                                            wire:model="editingCategory" 
                                                            id="editingCategory"
                                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                                                        >
                                                            <option value="static">Static</option>
                                                            <option value="dynamic">Dynamic</option>
                                                            <option value="multiple">Multiple</option>
                                                            <option value="unique">Unique</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <select 
                                                        wire:model="editingStatus" 
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                                                    >
                                                        <option value="pending">Pending</option>
                                                        <option value="success">Success</option>
                                                        <option value="failed">Failed</option>
                                                    </select>
                                                </td>
                                                <td class="px-6 py-4 flex space-x-2">
                                                    <button 
                                                        wire:click="updateProperty" 
                                                        class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700"
                                                    >
                                                        Save
                                                    </button>
                                                    <button 
                                                        wire:click="cancelEdit" 
                                                        class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                                    >
                                                        Cancel
                                                    </button>
                                                </td>
                                            @else
                                                <td class="px-6 py-4">{{ $property->name }}</td>
                                                <td class="px-6 py-4">{{ $property->type->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4">{{ ucfirst($property->category) }}</td>
                                                <td class="px-6 py-4">{{ ucfirst($property->status) }}</td>
                                                <td class="px-6 py-4 flex space-x-2">
                                                    <button 
                                                        wire:click="editProperty({{ $property->id }})"
                                                        class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button 
                                                        wire:click="deleteProperty({{ $property->id }})"
                                                        class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700"
                                                        onclick="return confirm('Are you sure you want to delete this property?')"
                                                    >
                                                        Delete
                                                    </button>


                                                    @if($property->category != 'dynamic')
                                                    <button 
                                                        wire:click="showPropertyItems({{ $property->id }})"
                                                        class="px-3 py-1 bg-purple-600 text-white rounded-md hover:bg-purple-700"
                                                    >
                                                        Items
                                                    </button>

                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Property Items Modal -->
                @if($showItemsModal)
                <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay - Cliquer pour fermer -->
                        <div wire:click="closeItemsModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity cursor-pointer" aria-hidden="true"></div>

                        <!-- Modal panel -->
                        <div class="inline-block align-bottom bg-white dark:bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white dark:bg-gray-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">Manage Property Items</h3>
                                    <!-- Bouton X pour fermer -->
                                    <button wire:click="closeItemsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="mt-4">
                                    <!-- Add Property Item Form -->
                                    <form wire:submit.prevent="addPropertyItem">
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <label for="newItemValue" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Value</label>
                                                <input 
                                                    wire:model="newItemValue" 
                                                    type="text" 
                                                    id="newItemValue" 
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                    placeholder="e.g., Red"
                                                    required
                                                >
                                            </div>
                                            <div>
                                                <label for="newItemStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                                                <select 
                                                    wire:model="newItemStatus" 
                                                    id="newItemStatus" 
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                                    required
                                                >
                                                    <option value="pending">Pending</option>
                                                    <option value="success">Success</option>
                                                    <option value="failed">Failed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <button 
                                                type="submit" 
                                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                                Add Item
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Property Items List -->
                                    <div class="mt-6">
                                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-2">Property Items</h4>
                                        @if($propertyItems->isEmpty())
                                            <p class="text-gray-500 dark:text-gray-400">No items for this property.</p>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($propertyItems as $item)
                                                    @if($editingItemId === $item->id)
                                                        <!-- Édition d'un item -->
                                                        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md border">
                                                            <form wire:submit.prevent="updatePropertyItem">
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                                    <div>
                                                                        <input 
                                                                            wire:model="editingItemValue" 
                                                                            type="text" 
                                                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                                                                            placeholder="Enter value"
                                                                            required
                                                                        >
                                                                    </div>
                                                                    <div>
                                                                        <select 
                                                                            wire:model="editingItemStatus" 
                                                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                                                                            required
                                                                        >
                                                                            <option value="pending">Pending</option>
                                                                            <option value="success">Success</option>
                                                                            <option value="failed">Failed</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="flex space-x-2 mt-3">
                                                                    <button 
                                                                        type="submit"
                                                                        class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                                                                    >
                                                                        Save
                                                                    </button>
                                                                    <button 
                                                                        type="button"
                                                                        wire:click="cancelItemEdit" 
                                                                        class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm"
                                                                    >
                                                                        Cancel
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <!-- Affichage normal d'un item -->
                                                        <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700">
                                                            <span class="text-gray-700 dark:text-gray-300">
                                                                <span class="font-medium">{{ $item->value }}</span>
                                                                <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                                                    @if($item->status === 'success') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200
                                                                    @elseif($item->status === 'failed') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200
                                                                    @else bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200 @endif">
                                                                    {{ ucfirst($item->status) }}
                                                                </span>
                                                            </span>
                                                            <div class="flex space-x-2">
                                                                <button 
                                                                    wire:click="editPropertyItem({{ $item->id }})"
                                                                    class="px-2 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
                                                                    title="Edit item"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                    </svg>
                                                                </button>
                                                                <button 
                                                                    wire:click="deletePropertyItem({{ $item->id }})"
                                                                    class="px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm"
                                                                    title="Delete item"
                                                                    onclick="return confirm('Are you sure you want to delete this item?')"
                                                                >
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button 
                                    wire:click="closeItemsModal" 
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('reset-messages', () => {
                window.setTimeout(() => {
                    @this.resetMessages();
                }, 3000);
            });
        });
    </script>
    @endvolt
</x-layouts.admin>