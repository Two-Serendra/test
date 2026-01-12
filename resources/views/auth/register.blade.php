<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Logo -->
    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG.png') }}"
        style="height: 100%; width: auto; object-fit: contain;" alt="2serendra" />

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>



        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit -->
        <div class="mt-4">
            <!-- <x-primary-button class="w-full flex justify-center items-center text-center"
                style="background-color: #008b26;">
                {{ __('Register') }}
            </x-primary-button> -->

            <x-primary-button type="submit" id="loginBtn"
                class="w-full flex justify-center items-center text-center relative cursor-pointer transition-colors duration-200"
                style="background-color: #008b26; color: white;">
                <!-- Spinner (hidden by default) -->
                <svg id="btnSpinner" class="h-5 w-5 text-white mr-3 hidden" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>

                <span id="btnText">Submit</span>
            </x-primary-button>
        </div>
        </div>



        <!-- Already Registered -->
        <div class="mt-2 text-center">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 
                      dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 
                      focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}">
                {{ __('Already registered? Login') }}
            </a>
        </div>
    </form>
</x-guest-layout>

<script>
    const btn = document.getElementById('loginBtn');
    const spinner = document.getElementById('btnSpinner');
    const text = document.getElementById('btnText');

    document.querySelector('form').addEventListener('submit', function (e) {

        if (btn.disabled) return;
        btn.disabled = true;
        btn.style.backgroundColor = '#007a20';
        spinner.classList.remove('hidden');
        spinner.style.animation = "spin 1s linear infinite";
        text.textContent = 'Submitting...';
    });

    // Hover effect: only if button is enabled
    btn.addEventListener('mouseenter', function () {
        if (!btn.disabled) btn.style.backgroundColor = '#009432';
    });
    btn.addEventListener('mouseleave', function () {
        if (!btn.disabled) btn.style.backgroundColor = '#008b26';
    });

    const style = document.createElement('style');
    style.innerHTML = `
@keyframes spin { 
    0% { transform: rotate(0deg); } 
    100% { transform: rotate(360deg); } 
}
`;
    document.head.appendChild(style);
</script>