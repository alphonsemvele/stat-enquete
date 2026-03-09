<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;

name('form');
middleware(['auth', 'verified']);

new class extends Component {
    public $title = '';
    public $description = '';
    public $questions = [];
    public $newQuestion = '';
    public $responses = [];
    public $activeTab = 'questions'; // Default tab
    public $isPublic = false; // Form visibility setting
    public $collectResponses = true; // Response collection setting
    public $settingsSaved = false; // Track settings save status

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->settingsSaved = false; // Reset success message when switching tabs
    }

    public function addQuestion()
    {
        $question = trim($this->newQuestion);
        if ($question) {
            $this->questions[] = ['text' => $question, 'type' => 'text', 'required' => false];
            $this->newQuestion = '';
        }
    }

    public function saveResponse($index, $response)
    {
        $this->responses[$index] = $response;
    }

    public function clearResponses()
    {
        $this->responses = [];
    }

    public function saveSettings()
    {
        // Simulate saving settings (e.g., to database)
        $this->settingsSaved = true;
        // Reset success message after 3 seconds
        $this->dispatch('reset-settings-saved')->self()->delay(3);
    }

    public function resetSettingsSaved()
    {
        $this->settingsSaved = false;
    }
};
?>
<x-layouts.app title="CAMTEL Form">
    @volt
    <div class="w-full bg-gray-100 dark:bg-gray-800 p-6 font-['Roboto',sans-serif]">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                    <li class="me-2">
                        <button
                            wire:click="setTab('questions')"
                            class="inline-flex items-center justify-center p-4 {{ $activeTab === 'questions' ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }} rounded-t-lg group"
                            aria-current="{{ $activeTab === 'questions' ? 'page' : '' }}"
                        >
                            <svg class="w-4 h-4 me-2 {{ $activeTab === 'questions' ? 'text-blue-600 dark:text-blue-500' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                                <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
                            </svg>Questions
                        </button>
                    </li>
                    <li class="me-2">
                        <button
                            wire:click="setTab('responses')"
                            class="inline-flex items-center justify-center p-4 {{ $activeTab === 'responses' ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }} rounded-t-lg group"
                            aria-current="{{ $activeTab === 'responses' ? 'page' : '' }}"
                        >
                            <svg class="w-4 h-4 me-2 {{ $activeTab === 'responses' ? 'text-blue-600 dark:text-blue-500' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 11.424V1a1 1 0 1 0-2 0v10.424a3.228 3.228 0 0 0 0 6.152V19a1 1 0 1 0 2 0v-1.424a3.228 3.228 0 0 0 0-6.152ZM19.25 14.5A3.243 3.243 0 0 0 17 11.424V1a1 1 0 0 0-2 0v10.424a3.227 3.227 0 0 0 0 6.152V19a1 1 0 1 0 2 0v-1.424a3.243 3.243 0 0 0 2.25-3.076Zm-6-9A3.243 3.243 0 0 0 11 2.424V1a1 1 0 0 0-2 0v1.424a3.228 3.228 0 0 0 0 6.152V19a1 1 0 1 0 2 0V8.576A3.243 3.243 0 0 0 13.25 5.5Z"/>
                            </svg>Responses
                        </button>
                    </li>
                    <li class="me-2">
                        <button
                            wire:click="setTab('settings')"
                            class="inline-flex items-center justify-center p-4 {{ $activeTab === 'settings' ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-500 dark:border-blue-500' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }} rounded-t-lg group"
                            aria-current="{{ $activeTab === 'settings' ? 'page' : '' }}"
                        >
                            <svg class="w-4 h-4 me-2 {{ $activeTab === 'settings' ? 'text-blue-600 dark:text-blue-500' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                                <path d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2 2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1 1 0 1 1 0 2Z"/>
                            </svg>Settings
                        </button>
                    </li>
                </ul>
            </div>

          

            <!-- Questions Section -->
            @if ($activeTab === 'questions')

              <!-- Header Section (Title and Description) -->
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">Create a Form</h1>
                <input
                    wire:model.live="title"
                    class="block w-full p-3 border border-gray-300 dark:border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 dark:bg-white dark:text-gray-900 placeholder-gray-400 transition-all duration-200 opacity-100"
                    placeholder="Form Title"
                >
                <textarea
                    wire:model.live="description"
                    class="block w-full p-3 mt-4 border border-gray-300 dark:border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 dark:bg-white dark:text-gray-900 placeholder-gray-400 transition-all duration-200 opacity-100"
                    placeholder="Description (optional)"
                    rows="3"
                ></textarea>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Questions</h2>
                <div class="space-y-4">
                    @foreach ($questions as $index => $question)
                        <div class="flex items-center space-x-4">
                            <input
                                wire:model.live="questions.{$index}.text"
                                class="block flex-1 p-3 border border-gray-300 dark:border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 dark:bg-white dark:text-gray-900 placeholder-gray-400 transition-all duration-200 opacity-100"
                                placeholder="Enter a question"
                            >
                            <select
                                wire:model.live="questions.{$index}.type"
                                class="block p-3 border border-gray-300 dark:border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 dark:bg-white dark:text-gray-900 transition-all duration-200 opacity-100"
                            >
                                <option value="text">Text</option>
                                <option value="multiple">Multiple Choice</option>
                                <option value="checkbox">Checkboxes</option>
                            </select>
                            <label class="flex items-center text-gray-700 dark:text-gray-300">
                                <input
                                    wire:model.live="questions.{$index}.required"
                                    type="checkbox"
                                    class="mr-2 rounded border-gray-300 text-blue-500 focus:ring-blue-400"
                                >
                                Required
                            </label>
                            <button
                                wire:ignore.self
                                wire:click="saveResponse({$index}, $questions[{$index}]['text'])"
                                class="block relative bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 focus:ring-2 focus:ring-green-400 focus:ring-opacity-50 transition-all duration-200 font-medium z-10 opacity-100"
                            >
                                Save
                            </button>
                        </div>
                        @if (isset($responses[$index]))
                            <p class="text-gray-600 dark:text-gray-400 ml-4 mt-2">Saved Response: {{ $responses[$index] }}</p>
                        @endif
                    @endforeach
                    <!-- Add Question Block -->
                    <div class="mt-6 bg-white dark:bg-gray-50 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-200 transition-all duration-300">
                        <div class="flex items-center space-x-3">
                            <input
                                wire:model.live="newQuestion"
                                class="block flex-1 p-3 border border-gray-300 dark:border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 dark:bg-white dark:text-gray-900 placeholder-gray-400 transition-all duration-200 opacity-100"
                                placeholder="Add a new question..."
                            >
                            <button
                                wire:ignore.self
                                wire:click="addQuestion"
                                class="block relative bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 transition-all duration-200 font-medium z-10 opacity-100"
                            >
                                Add Question
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Responses Section -->
            @if ($activeTab === 'responses')
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Responses</h2>
                    @if (!empty($responses))
                    <button
                        wire:ignore.self
                        wire:click="clearResponses"
                        class="block relative bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 focus:ring-2 focus:ring-red-400 focus:ring-opacity-50 transition-all duration-200 font-medium z-10 opacity-100"
                    >
                        Clear All Responses
                    </button>
                    @endif
                </div>
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @if (empty($responses))
                        <p class="text-gray-600 dark:text-gray-400">No responses yet.</p>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($responses as $index => $response)
                                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="text-gray-700 dark:text-gray-300 font-medium">Question {{ $index + 1 }}: {{ $questions[$index]['text'] }}</p>
                                    <p class="text-gray-600 dark:text-gray-400">Response: {{ $response }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Settings Section -->
            @if ($activeTab === 'settings')
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Settings</h2>
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <label class="text-gray-700 dark:text-gray-300 font-medium">Public Form</label>
                        <input
                            wire:model.live="isPublic"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-500 focus:ring-blue-400"
                        >
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="text-gray-700 dark:text-gray-300 font-medium">Collect Responses</label>
                        <input
                            wire:model.live="collectResponses"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-500 focus:ring-blue-400"
                        >
                    </div>
                    <div>
                        <button
                            wire:ignore.self
                            wire:click="saveSettings"
                            class="block relative bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 transition-all duration-200 font-medium z-10 opacity-100"
                        >
                            Save Settings
                        </button>
                    </div>
                </div>
                @if ($settingsSaved)
                    <p class="text-green-600 dark:text-green-400 mt-4">Settings saved successfully!</p>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endvolt
</x-layouts.app>