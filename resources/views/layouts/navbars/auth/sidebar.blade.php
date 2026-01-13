@php
$menu = config('sidebar');
$context = session('context', 'pssk'); // default
@endphp

<aside id="sidenav-main"
    class="sidenav navbar navbar-vertical navbar-expand-xs bg-white border-0 border-radius-xl fixed-start overflow-y-hidden h-100vh">
    <div class="sidenav-sticky-header sticky-top bg-white">
        <div class="sidenav-header text-center">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0 d-flex justify-content-center" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/img/Logo-kedokteran-untar.png') }}" class="navbar-brand-img h-100"
                    alt="Logo">
            </a>
        </div>
        @hasanyrole('admin|lecturer|koordinator')
        @php
        $currentContext = request()->route('context') ?? session('context');
        @endphp

        <ul class="nav d-flex justify-content-between flex-nowrap mx-4">
            @foreach (['pssk' => 'PSSK', 'pspd' => 'PSPD'] as $key => $label)
            <li class="nav-item mx-2 flex-fill">
                <a href="{{ route('set.context', $key) }}" class="nav-link text-center shadow border-radius-md d-flex align-items-center justify-content-center fw-semibold
           {{ $currentContext === $key ? 'fw-bold text-primary bg-white' : 'text-muted' }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>
        @endhasanyrole
    </div>
    <div id="sidenav-collapse-main" class="collapse navbar-collapse ">
        <ul class="navbar-nav">
            <!-- User Dropdown - Desktop Only -->
            @foreach($menu as $section)
            @if(
            auth()->user()->hasAnyRole($section['roles']) &&
            (
            !isset($section['context']) ||
            in_array($context, $section['context'])
            )
            )
            @if($section['title'])
            <x-sidebar-title title="{{ $section['title'] }}" />
            @endif

            @foreach($section['items'] as $item)
            @if(!isset($item['roles']) || auth()->user()->hasAnyRole($item['roles']))
            @php
            $active = request()->is($item['pattern']);
            // Menyesuaikan pattern dengan context jika ada placeholder {context}
            $pattern = isset($item['pattern'])
            ? str_replace('{context}', $context, $item['pattern'])
            : '';
            @endphp

            @if(isset($item['route']))
            {{-- Menggunakan route --}}
            <x-sidebar-item :route="$item['route']" :params="$item['params'] ?? []" :pattern="$pattern"
                :icon="$item['icon']" :label="$item['label']" />
            @elseif(isset($item['url']))
            {{-- Fallback untuk URL lama --}}
            @php
            $url = str_replace('{context}', $context, $item['url']);
            @endphp
            <x-sidebar-item :url="$url" :pattern="$pattern" :icon="$item['icon']" :label="$item['label']" />
            @endif
            @endif
            @endforeach
            @endif
            @endforeach

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/logout') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                    </div>
                    <span class="nav-link-text ms-1">Sign Out</span>
                </a>
            </li>
        </ul>
    </div>
</aside>