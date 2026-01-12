 <x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <img src="{{ asset('assets/images/TWO SERENDRA LOGO PNG.png') }}"
        style="height: 100%; width: auto; object-fit: contain;" alt="2serendra" />
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

    
          <div class="mt-4">
    <x-primary-button 
        type="submit" 
        id="loginBtn" 
        class="w-full flex justify-center items-center text-center relative cursor-pointer transition-colors duration-200"
        style="background-color: #008b26; color: white;"
    >
        <!-- Spinner (hidden by default) -->
        <svg id="btnSpinner" class="h-5 w-5 text-white mr-3 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>

        <span id="btnText">Log in</span>
    </x-primary-button>
</div>

            @if (Route::has('register'))
                <div class="mt-2 text-center">
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        href="{{ route('register') }}">
                        {{ __('Don\'t have an account? Register') }}
                    </a>
                </div>
            @endif

        </div>
    </form>
</x-guest-layout>

<script>
const btn = document.getElementById('loginBtn');
const spinner = document.getElementById('btnSpinner');
const text = document.getElementById('btnText');

document.querySelector('form').addEventListener('submit', function(e) {
   
    if(btn.disabled) return;
    btn.disabled = true;
    btn.style.backgroundColor = '#007a20'; 
    spinner.classList.remove('hidden');
    spinner.style.animation = "spin 1s linear infinite";
    text.textContent = 'Logging in...';
});

// Hover effect: only if button is enabled
btn.addEventListener('mouseenter', function() {
    if(!btn.disabled) btn.style.backgroundColor = '#009432'; 
});
btn.addEventListener('mouseleave', function() {
    if(!btn.disabled) btn.style.backgroundColor = '#008b26'; 
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