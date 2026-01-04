<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Project') }}
            </h2>
            <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Projects') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('projects.store') }}">
                        @csrf

                        <!-- Project Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Project Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                   placeholder="My Newsletter Project">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Project Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                      placeholder="Brief description of your newsletter project...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Allowed Origins -->
                        <div class="mb-6">
                            <label for="allowed_origins" class="block text-sm font-medium text-gray-700 mb-2">
                                Allowed Origins (CORS)
                            </label>
                            <textarea name="allowed_origins" 
                                      id="allowed_origins" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('allowed_origins') border-red-500 @enderror"
                                      placeholder="https://example.com&#10;https://mysite.com&#10;*">{{ old('allowed_origins') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Enter one domain per line. Use * to allow all origins (not recommended for production).
                            </p>
                            @error('allowed_origins')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Settings Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Email Settings</h3>
                            
                            <!-- Double Opt-in -->
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="double_opt_in" 
                                           id="double_opt_in" 
                                           value="1"
                                           {{ old('double_opt_in', config('newsletter.double_opt_in_default')) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="double_opt_in" class="ml-2 block text-sm text-gray-900">
                                        Enable Double Opt-in
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Require email confirmation before adding subscribers to your list.
                                </p>
                            </div>

                            <!-- Welcome Email -->
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="welcome_email" 
                                           id="welcome_email" 
                                           value="1"
                                           {{ old('welcome_email') ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="welcome_email" class="ml-2 block text-sm text-gray-900">
                                        Send Welcome Email
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Send a welcome email to new subscribers after confirmation.
                                </p>
                            </div>

                            <!-- Admin Notifications -->
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           name="admin_notifications" 
                                           id="admin_notifications" 
                                           value="1"
                                           {{ old('admin_notifications') ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="admin_notifications" class="ml-2 block text-sm text-gray-900">
                                        Enable Admin Notifications
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Receive email notifications for new subscriptions.
                                </p>
                            </div>
                        </div>

                        <!-- Project Status -->
                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select name="status" 
                                    id="status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('projects.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>