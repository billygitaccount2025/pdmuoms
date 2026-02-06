<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @yield('head')

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('DILG-Logo.png') }}" alt="DILG Logo" style="height: 40px; margin-bottom: 5px;">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <script>
        // Confirmation for save/update/delete actions
        (function attachActionConfirms() {
            const defaultMessages = {
                save: 'Are you sure you want to save these changes?',
                delete: 'Are you sure you want to delete this item? This action cannot be undone.'
            };

            function getActionText(el) {
                const text = (el.textContent || el.value || '').trim().toLowerCase();
                return text;
            }

            function hasInlineConfirm(el) {
                const onclick = el.getAttribute('onclick') || '';
                return onclick.includes('confirm(') || onclick.includes('deleteDocument(');
            }

            function formHasInlineConfirm(el) {
                const form = el.closest('form');
                if (!form) return false;
                const onsubmit = form.getAttribute('onsubmit') || '';
                return onsubmit.includes('confirm(');
            }

            function needsAutoConfirm(el) {
                if (!el || el.disabled) return false;
                if (el.dataset && el.dataset.confirmSkip === 'true') return false;
                if (el.dataset && el.dataset.confirm) return true;
                if (hasInlineConfirm(el) || formHasInlineConfirm(el)) return false;
                const text = getActionText(el);
                if (!text) return false;
                const isSave = text.includes('save');
                const isDelete = text.includes('delete');
                return isSave || isDelete;
            }

            function resolveMessage(el) {
                if (el.dataset && el.dataset.confirm) return el.dataset.confirm;
                const text = getActionText(el);
                return text.includes('delete') ? defaultMessages.delete : defaultMessages.save;
            }

            document.addEventListener('click', function(e) {
                const target = e.target.closest('button, input[type="submit"], input[type="button"], a');
                if (!target) return;
                if (!needsAutoConfirm(target)) return;
                const message = resolveMessage(target);
                if (!window.confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
                if (target.dataset) {
                    target.dataset.confirmed = 'true';
                }
            }, true);

            document.addEventListener('submit', function(e) {
                const submitter = e.submitter;
                if (!submitter) return;
                if (submitter.dataset && submitter.dataset.confirmed === 'true') {
                    delete submitter.dataset.confirmed;
                    return;
                }
                if (!needsAutoConfirm(submitter)) return;
                const message = resolveMessage(submitter);
                if (!window.confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
        })();
    </script>
</body>
</html>
