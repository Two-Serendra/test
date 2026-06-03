@extends('layouts.app')

@section('title', 'Bridge Demo — Dashboard')

@section('content')
<div class="page" x-data="dashboardPage">

    {{-- Loading ──────────────────────────────────────────────── --}}
    <div class="loading" x-show="$store.superapp.isLoading">
        <p>Waiting for shell context…</p>
    </div>

    {{-- Dev warning (not inside the shell) ──────────────────── --}}
    <div class="warning-banner" x-show="!inShell && !$store.superapp.isLoading">
        Running outside the shell — bridge data is unavailable.
        In production this page runs inside the shell iframe.
    </div>

    <div x-show="!$store.superapp.isLoading">

        {{-- User ─────────────────────────────────────────────── --}}
        <div class="card">
            <h2>User</h2>
            <dl>
                <dt>Name</dt>  <dd x-text="$store.superapp.user?.name  ?? '—'"></dd>
                <dt>Email</dt> <dd x-text="$store.superapp.user?.email ?? '—'"></dd>
                <dt>Role</dt>  <dd x-text="$store.superapp.user?.role  ?? '—'"></dd>
            </dl>

            <template x-if="$store.superapp.accounts.length > 1">
                <div>
                    <p class="muted" style="margin:14px 0 8px;font-size:12px;text-transform:uppercase;letter-spacing:.06em;font-weight:600">Linked Accounts</p>
                    <template x-for="(acc, i) in $store.superapp.accounts" :key="acc.email">
                        <div :class="i > 0 ? 'unit-row unit-row--divider' : 'unit-row'">
                            <dl>
                                <dt>Name</dt>  <dd x-text="acc.name"></dd>
                                <dt>Email</dt> <dd x-text="acc.email"></dd>
                            </dl>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Unit/s ───────────────────────────────────────────── --}}
        <div class="card">
            <h2>Unit/s</h2>
            <p class="muted" x-show="$store.superapp.units.length === 0">
                No units — ADMIN or VENDOR account.
            </p>
            <template x-for="(u, i) in $store.superapp.units" :key="u.id">
                <div :class="i > 0 ? 'unit-row unit-row--divider' : 'unit-row'">
                    <dl>
                        <dt>Name</dt> <dd x-text="u.name"></dd>
                        <dt>Role</dt> <dd x-text="u.role"></dd>
                    </dl>
                </div>
            </template>
        </div>

        {{-- Role-gated content ───────────────────────────────── --}}
        <div class="card role-resident" x-show="$store.superapp.user?.role === 'RESIDENT'">
            <h3>Resident Area</h3>
            <p>Content visible only to residents.</p>
        </div>

        <div class="card role-admin" x-show="$store.superapp.user?.role === 'ADMIN'">
            <h3>Admin Controls</h3>
            <p>Management tools visible only to admins.</p>
        </div>

        <div class="card role-vendor" x-show="$store.superapp.user?.role === 'VENDOR'">
            <h3>Vendor Panel</h3>
            <p>Vendor-specific content and tools.</p>
        </div>

        {{-- Auth token & API call demo ───────────────────────── --}}
        <div class="card">
            <h2>Auth Token &amp; API Calls</h2>
            <p class="muted">
                Token:
                <code x-text="$store.superapp.token
                    ? $store.superapp.token.substring(0, 24) + '…'
                    : '—'">
                </code>
            </p>
            <br>
            <button class="btn" @click="fetchUnitData()">
                Fetch unit data (check console)
            </button>
            <p class="status-msg" x-text="apiStatus" x-show="apiStatus !== ''"></p>
        </div>

        {{-- Header controls ──────────────────────────────────── --}}
        <div class="card" x-data="headerControls">
            <h2>Header Controls</h2>

            <div class="form-group">
                <label>Mode</label>
                <select x-model="mode">
                    <option value="floating">floating — pill only, no bar</option>
                    <option value="sticky-no-back">sticky-no-back — bar, no back button</option>
                    <option value="sticky">sticky — bar with back button</option>
                </select>
            </div>

            <div class="form-group">
                <label>Title</label>
                <input type="text" x-model="title" placeholder="e.g. Bridge Demo">
            </div>

            <div class="form-group">
                <label>Subtitle</label>
                <input type="text" x-model="subtitle" placeholder="Optional subtext">
            </div>

            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label>Background color</label>
                    <div class="color-row">
                        <input type="color" x-model="backgroundColor" style="width:40px;height:36px;padding:2px;border-radius:6px;border:1px solid #d1d5db;cursor:pointer">
                        <input type="text" x-model="backgroundColor" placeholder="#1e3a5f" style="flex:1">
                    </div>
                </div>
                <div class="form-group" style="flex:1">
                    <label>Text style</label>
                    <select x-model="textStyle">
                        <option value="white">white</option>
                        <option value="black">black</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" x-model="showHome">
                    Show home icon
                </label>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
                <button class="btn" @click="applyHeader()">Apply Header</button>
                <button class="btn btn-secondary" @click="$store.superapp.bridge?.toggleHeader(false)">Hide Header</button>
                <button class="btn btn-secondary" @click="$store.superapp.bridge?.toggleHeader(true)">Show Header</button>
            </div>
        </div>

        {{-- Navigation to other example pages ───────────────── --}}
        <div class="card">
            <h2>Example Pages</h2>
            <div class="nav-links">
                <a href="/amenities" class="btn">
                    Amenities — sticky-no-back
                </a>
                <a href="/book-facility" class="btn">
                    Book Facility — sticky + back
                </a>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('headerControls', () => ({
        mode: 'sticky-no-back',
        title: 'Bridge Demo',
        subtitle: '',
        backgroundColor: '#1e3a5f',
        textStyle: 'white',
        showHome: false,

        applyHeader() {
            const payload = { mode: this.mode, textStyle: this.textStyle };
            if (this.title)           payload.title           = this.title;
            if (this.subtitle)        payload.subtitle        = this.subtitle;
            if (this.backgroundColor) payload.backgroundColor = this.backgroundColor;
            payload.showHome = this.showHome;
            Alpine.store('superapp').bridge?.setHeader(payload);
        },
    }));

    Alpine.data('dashboardPage', () => ({
        inShell: window.self !== window.top,
        apiStatus: '',

        init() {
            // Root page of the miniapp — sticky bar, no back button
            Alpine.store('superapp').bridge?.setHeader({
                mode: 'sticky-no-back',
                title: 'Bridge Demo',
                backgroundColor: '#1e3a5f',
                textStyle: 'white',
                showHome: false,
            });
        },

        async fetchUnitData() {
            const store = Alpine.store('superapp');
            if (!store.unit || !store.token) {
                this.apiStatus = 'No unit or token — not running inside the shell.';
                return;
            }
            this.apiStatus = 'Fetching…';
            try {
                const res = await fetch(`/api/unit-data?unitId=${store.unit.id}`, {
                    headers: { Authorization: `Bearer ${store.token}` },
                });
                const body = await res.text();
                console.log('[Bridge Demo] API response:', body);
                this.apiStatus = `Response status: ${res.status}`;
            } catch (err) {
                this.apiStatus = `Error: ${err.message}`;
            }
        },
    }));
});
</script>
@endsection
