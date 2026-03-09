<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Http\Middleware\RoleBasedRedirect;
use App\Models\Icone;

name('admin.icones');
// middleware(['auth', 'verified', RoleBasedRedirect::class]);

new class extends Component {
    public $name = '';
    public $value = '';
    public $status = 'pending';
    public $successMessage = '';
    public $errorMessage = '';
    public $sidebarOpen = false;
    public $editingIconeId = null;
    public $editingName = '';
    public $editingValue = '';
    public $editingStatus = 'pending';

    public function mount()
    {
        $this->resetMessages();
    }

    public function addIcone()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string',
            'status' => 'required|in:pending,success,failed',
        ]);

        try {
            Icone::create([
                'name' => $this->name,
                'value' => $this->value,
                'status' => $this->status,
            ]);

            $this->successMessage = 'Icone added successfully!';
            $this->reset(['name', 'value', 'status']);
            $this->dispatch('reset-messages');
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to add icone: ' . $e->getMessage();
        }
    }

    public function editIcone($id)
    {
        $icone = Icone::findOrFail($id);
        $this->editingIconeId = $id;
        $this->editingName = $icone->name;
        $this->editingValue = $icone->value;
        $this->editingStatus = $icone->status;
    }

    public function updateIcone()
    {

        // dd($this->editingIconeId);
        $this->validate([
            'editingName' => 'required|string|max:255',
            'editingValue' => 'required|string',
            'editingStatus' => 'required|in:pending,success,failed',
        ]);

        try {
            $icone = Icone::findOrFail($this->editingIconeId);
            $icone->update([
                'name' => $this->editingName,
                'value' => $this->editingValue,
                'status' => $this->editingStatus,
            ]);

            $this->successMessage = 'Icone updated successfully!';
            $this->reset(['editingIconeId', 'editingName', 'editingValue', 'editingStatus']);
            $this->dispatch('reset-messages');
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update icone: ' . $e->getMessage();
        }
    }

    public function deleteIcone($id)
    {
        try {
            $icone = Icone::findOrFail($id);
            $icone->delete();
            $this->successMessage = 'Icone deleted successfully!';
            $this->dispatch('reset-messages')->self()->delay(3);

            if ($this->editingIconeId === $id) {
                $this->reset(['editingIconeId', 'editingName', 'editingValue', 'editingStatus']);
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to delete icone: ' . $e->getMessage();
        }
    }

    public function cancelEdit()
    {
        $this->reset(['editingIconeId', 'editingName', 'editingValue', 'editingStatus']);
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

    public function getIconesProperty()
    {
        return Icone::all();
    }
};
?>

<x-layouts.admin title="Admin - Icone Management">
    @volt
    <div class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-800 font-['Roboto',sans-serif]">
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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Icone Management</h1>

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

                <!-- Add Icone Form -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Add New Icone</h2>
                    <form wire:submit="addIcone">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                                <input 
                                    wire:model="name" 
                                    type="text" 
                                    id="name" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., Home Icon"
                                    required
                                >
                            </div>
                            <div class="md:col-span-2">
                                <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SVG Code</label>
                                <textarea 
                                    wire:model="value" 
                                    id="value" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="e.g., <svg>...</svg>"
                                    rows="4"
                                    required
                                ></textarea>
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
                                Add Icone
                            </button>
                        </div>
                    </form>

                    
                </div>

                <!-- Icones Table -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Available Icones</h2>
                    @if($this->icones->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">No icones available.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Name</th>
                                        <th scope="col" class="px-6 py-3">SVG Preview</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->icones as $icone)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            @if($editingIconeId === $icone->id)
                                                <td class="px-6 py-4">
                                                    <input 
                                                        wire:model="editingName" 
                                                        type="text" 
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                                                        placeholder="Enter name"
                                                    >
                                                </td>
                                                <td class="px-6 py-4">
                                                    <textarea 
                                                        wire:model="editingValue" 
                                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                                                        placeholder="Enter SVG code"
                                                        rows="4"
                                                    ></textarea>
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
                                                        wire:click="updateIcone" 
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
                                                <td class="px-6 py-4">{{ $icone->name }}</td>
                                                <td class="px-6 py-4">
                                                    <div class="w-6 h-6">{!! $icone->value !!}</div>
                                                </td>
                                                <td class="px-6 py-4">{{ ucfirst($icone->status) }}</td>
                                                <td class="px-6 py-4 flex space-x-2">
                                                    <button 
                                                        wire:click="editIcone({{ $icone->id }})"
                                                        class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button 
                                                        wire:click="deleteIcone({{ $icone->id }})"
                                                        class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700"
                                                        onclick="return confirm('Are you sure you want to delete this icone?')"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
      
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