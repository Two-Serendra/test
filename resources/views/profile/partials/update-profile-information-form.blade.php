<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email (disabled) -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                :value="old('email', $user->email)" disabled />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <!-- Resident Type -->
        <div>
            <x-input-label for="res_type" :value="__('Resident Type')" />
            <select id="res_type" name="res_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="Owner" {{ old('res_type', $user->res_type) === 'Owner' ? 'selected' : '' }}>Owner</option>
                <option value="Tenant" {{ old('res_type', $user->res_type) === 'Tenant' ? 'selected' : '' }}>Tenant
                </option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('res_type')" />
        </div>

        <!-- Section -->
        <div>
            <x-input-label for="section" :value="__('Section')" />
            <select id="section" name="section" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @foreach(['Almond', 'Belize', 'Callery', 'Dolce', 'Aston', 'Red Oak', 'Meranti', 'Sequoia'] as $section)
                    <option value="{{ $section }}" {{ old('section', $user->section) === $section ? 'selected' : '' }}>
                        {{ $section }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('section')" />
        </div>

        <!-- Unit Number -->
        <div>
            <x-input-label for="unit_no" :value="__('Unit No.')" />
            <x-text-input id="unit_no" name="unit_no" type="text" class="mt-1 block w-full" :value="old('unit_no', $user->unit_no)" required />
            <x-input-error class="mt-2" :messages="$errors->get('unit_no')" />
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>