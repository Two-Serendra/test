<x-guest-layout>
    <style>
        .send-btn {
            height: 40px;
            padding: 0 16px;
            background-color: #2563eb;
            /* blue */
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid #1d4ed8;
            cursor: pointer;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .send-btn:hover {
            background-color: #1d4ed8;
            border-color: #1e40af;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .send-btn:disabled {
            background-color: #9ca3af;
            border-color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
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
            <x-input-error :messages="$errors->get('invite_token')" class="mt-2" />
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

        <div class="mt-4">
            <x-input-label for="invite_token" value="Registration Token" />

            <div class="flex items-center">
                <div class="flex-1 min-w-0">
                    <x-text-input id="invite_token" name="invite_token" :value="old('invite_token')" type="text"
                        class="w-full h-10" />
                </div>

                <button type="button" onclick="sendToken()" id="sendTokenBtn"
                    class="send-btn ml-3 px-3 h-10 w-10 flex items-center justify-center shrink-0">

                    <!-- Spinner -->
                    <svg id="sendSpinner" class="h-4 w-4 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25">
                        </circle>
                        <path fill="currentColor" class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>

                    <!-- Icon (ONLY idle state) -->
                    <svg id="sendIcon" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M22 2L11 13"></path>
                        <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                    </svg>

                    <!-- Timer (ONLY cooldown state) -->
                    <span id="sendTimer" class="hidden text-xs sm:text-sm"></span>

                </button>
            </div>

            <p id="tokenMessage" class="text-sm text-gray-500 mt-1"></p>
        </div>

        <!-- Submit -->
        <div class="mt-4">
            <x-primary-button type="submit" id="registerBtn"
                class="w-full flex justify-center items-center text-center relative cursor-pointer transition-colors duration-200"
                style="background-color: #008b26; color: white;">
                <!-- Spinner -->
                <svg id="registerSpinner" class="h-5 w-5 text-white mr-3 hidden" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>

                <span id="registerText">Submit</span>
            </x-primary-button>
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
    document.addEventListener('DOMContentLoaded', function () {

        const registerBtn = document.getElementById('registerBtn');
        const registerSpinner = document.getElementById('registerSpinner');
        const registerText = document.getElementById('registerText');

        const email = document.getElementById('email');
        const token = document.getElementById('invite_token');

        function toggleRegister() {
            if (email.value.trim() && token.value.trim()) {
                registerBtn.disabled = false;
                registerBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                registerBtn.disabled = true;
                registerBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        email.addEventListener('input', toggleRegister);
        token.addEventListener('input', toggleRegister);

        toggleRegister();

        const form = registerBtn.closest('form');

        form.addEventListener('submit', function () {
            if (registerBtn.disabled) return;

            registerBtn.disabled = true;

            // spinner ON
            registerSpinner.classList.remove('hidden');

            // text change
            registerText.textContent = "Submitting...";

            // optional UX feedback
            registerBtn.style.backgroundColor = '#007a20';
        });

    });



    let cooldownInterval = null;

    window.sendToken = function () {
        const email = document.getElementById('email').value.trim();
        const tokenMessage = document.getElementById('tokenMessage');
        const btn = document.getElementById('sendTokenBtn');

        const spinner = document.getElementById('sendSpinner');
        const icon = document.getElementById('sendIcon');
        const timer = document.getElementById('sendTimer');

        if (btn.disabled) return;

        if (!email) {
            showMessage("Please enter your email first.", true);
            return;
        }

        btn.disabled = true;

        // UI → loading state
        spinner.classList.remove('hidden');
        icon.classList.add('hidden');

        fetch("{{ route('send.token') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ email })
        })
            .then(res => res.json())
            .then(data => {

                spinner.classList.add('hidden');

                // switch to cooldown UI (NO icon)
                icon.classList.add('hidden');
                timer.classList.remove('hidden');

                showMessage(data.message, false);

                startCooldown(btn, data.retry_after || 90);

                document.getElementById('invite_token').focus();
            })
            .catch(err => {

                btn.disabled = false;

                spinner.classList.add('hidden');
                icon.classList.remove('hidden');
                timer.classList.add('hidden');

                showMessage(err.message || "Something went wrong.", true);
            });
    };


    function startCooldown(btn, seconds) {
        let remaining = seconds;

        const icon = document.getElementById('sendIcon');
        const timer = document.getElementById('sendTimer');

        if (cooldownInterval) clearInterval(cooldownInterval);

        btn.disabled = true;

        cooldownInterval = setInterval(() => {

            timer.textContent = `${remaining}s`;
            remaining--;

            if (remaining < 0) {
                clearInterval(cooldownInterval);
                cooldownInterval = null;

                btn.disabled = false;

                // RESET → ICON ONLY (your requirement)
                timer.classList.add('hidden');
                icon.classList.remove('hidden');
                timer.textContent = "";
            }

        }, 1000);
    }

    function showMessage(msg, isError = false) {
        const tokenMessage = document.getElementById('tokenMessage');

        tokenMessage.innerText = msg;

        tokenMessage.classList.remove('text-red-500', 'text-green-500');
        tokenMessage.classList.add(isError ? 'text-red-500' : 'text-green-500');
    }
</script>