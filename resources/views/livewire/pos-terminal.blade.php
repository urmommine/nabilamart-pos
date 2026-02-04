<div class="h-full flex flex-col" x-data="posTerminal">
    <!-- Top Navigation Bar -->
    <header
        class="flex shrink-0 items-center justify-between whitespace-nowrap border-b border-solid border-border-light dark:border-border-dark px-6 py-3 bg-background-light dark:bg-background-dark z-50">
        <div class="flex items-center gap-4 text-slate-900 dark:text-white">
            <div class="size-10 flex items-center justify-center bg-primary/10 rounded-lg text-primary">
                <span class="material-symbols-outlined text-2xl">point_of_sale</span>
            </div>
            <div>
                <h2 class="text-slate-900 dark:text-white text-lg font-bold leading-tight tracking-tight">
                    {{ $storeName }}
                </h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="block size-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                    <span class="text-xs text-text-muted-light dark:text-text-muted-dark font-medium">
                        {{ config('app.version') }} • Online • {{ now()->format('d M Y') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-6" x-data="{ open: false }">
            <!-- Shortcut Hint -->
            <div
                class="hidden lg:flex gap-4 text-text-muted-light dark:text-text-muted-dark text-xs font-medium bg-surface-light dark:bg-surface-dark px-4 py-2 rounded-lg border border-border-light/50 dark:border-border-dark/50">
                <span class="flex items-center gap-1"><kbd
                        class="bg-slate-200 dark:bg-[#382929] px-1.5 py-0.5 rounded text-slate-700 dark:text-white border border-slate-300 dark:border-[#533c3d]">F2</kbd>
                    Cari</span>
                <span class="flex items-center gap-1"><kbd
                        class="bg-slate-200 dark:bg-[#382929] px-1.5 py-0.5 rounded text-slate-700 dark:text-white border border-slate-300 dark:border-[#533c3d]">F9</kbd>
                    Bayar</span>
                <span class="flex items-center gap-1"><kbd
                        class="bg-slate-200 dark:bg-[#382929] px-1.5 py-0.5 rounded text-slate-700 dark:text-white border border-slate-300 dark:border-[#533c3d]">F10</kbd>
                    Pajak</span>
            </div>
            <div class="flex gap-2">
                <!-- Connect Printer Button (Bluetooth & USB Web) -->
                @if($printerType === 'bluetooth' || $printerType === 'usb_web')
                    <button onclick="connectPrinter()" @click="printerGestureRequired = false"
                        class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-surface-light dark:bg-surface-dark text-slate-700 dark:text-white hover:bg-slate-200 dark:hover:bg-[#382929] transition-colors border border-border-light/50 dark:border-border-dark/50 relative"
                        :class="printerGestureRequired ? 'ring-2 ring-primary animate-pulse shadow-[0_0_15px_rgba(234,42,51,0.5)]' : ''"
                        title="Connect Printer ({{ $printerType === 'bluetooth' ? 'Bluetooth' : 'USB' }})">
                        <span
                            class="material-symbols-outlined">{{ $printerType === 'bluetooth' ? 'bluetooth' : 'usb' }}</span>
                        <!-- Notification Dot if gesture required -->
                        <template x-if="printerGestureRequired">
                            <span class="absolute top-1 right-1 size-2 bg-primary rounded-full border border-white"></span>
                        </template>
                    </button>
                @endif

                <!-- Theme Toggle Button -->
                <button @click="darkMode = !darkMode"
                    class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-surface-light dark:bg-surface-dark text-slate-700 dark:text-white hover:bg-slate-200 dark:hover:bg-[#382929] transition-colors border border-border-light/50 dark:border-border-dark/50"
                    title="Toggle Theme">
                    <span class="material-symbols-outlined" x-show="!darkMode">dark_mode</span>
                    <span class="material-symbols-outlined" x-show="darkMode" x-cloak>light_mode</span>
                </button>
                <button
                    class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-surface-light dark:bg-surface-dark text-slate-700 dark:text-white hover:bg-slate-200 dark:hover:bg-[#382929] transition-colors border border-border-light/50 dark:border-border-dark/50">
                    <span class="material-symbols-outlined">fullscreen</span>
                </button>
                <button
                    class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-surface-light dark:bg-surface-dark text-slate-700 dark:text-white hover:bg-slate-200 dark:hover:bg-[#382929] transition-colors relative border border-border-light/50 dark:border-border-dark/50">
                    <span class="material-symbols-outlined">notifications</span>
                    <span
                        class="absolute top-2.5 right-2.5 size-2 bg-primary rounded-full border-2 border-surface-light dark:border-surface-dark"></span>
                </button>
                <button
                    class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-surface-light dark:bg-surface-dark text-slate-700 dark:text-white hover:bg-slate-200 dark:hover:bg-[#382929] transition-colors border border-border-light/50 dark:border-border-dark/50"
                    @click="open = !open">
                    <span class="material-symbols-outlined">settings</span>
                </button>
            </div>

            <!-- User Menu Dropdown -->
            <div class="relative">
                <div class="flex items-center gap-3 pl-4 border-l border-border-light dark:border-border-dark cursor-pointer"
                    @click="open = !open">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-900 dark:text-white leading-none">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark mt-1">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                    <!-- Initial Avatar -->
                    <div
                        class="bg-primary/20 flex items-center justify-center text-primary font-bold rounded-lg size-10 ring-2 ring-border-light dark:ring-border-dark">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>

                <div x-show="open" @click.outside="open = false" x-cloak
                    class="absolute right-0 top-full mt-2 w-48 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-lg z-50 overflow-hidden">
                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                        class="block px-4 py-3 text-sm text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-[#382929] hover:text-slate-900 dark:hover:text-white transition-colors">
                        Dashboard Admin
                    </a>
                    <button wire:click="openProfileModal(); open = false"
                        class="w-full text-left block px-4 py-3 text-sm text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-[#382929] hover:text-slate-900 dark:hover:text-white transition-colors">
                        Profil Saya
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left block px-4 py-3 text-sm text-red-500 dark:text-red-400 hover:bg-slate-100 dark:hover:bg-[#382929] hover:text-red-600 dark:hover:text-red-300 transition-colors border-t border-border-light dark:border-border-dark">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    <!-- Main Layout -->
    <main class="flex flex-1 overflow-hidden relative" x-data="{ mobileCartOpen: false }">
        <!-- Left Panel: Inventory (70%) -->
        <section class="w-full lg:w-[70%] flex flex-col bg-background-light dark:bg-background-dark relative z-0">
            <!-- Sticky Filter Header -->
            <div
                class="sticky top-0 z-10 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-sm border-b border-border-light dark:border-border-dark px-6 pt-5 pb-0 shadow-sm">
                <!-- Customer Search & Product Search -->
                <div class="mb-4 space-y-3">
                    <!-- Customer Search -->
                    <div class="relative" x-data="{ open: false }">
                        <div
                            class="flex items-center gap-2 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl px-3 py-2 shadow-sm">
                            <span
                                class="material-symbols-outlined text-text-muted-light dark:text-text-muted-dark">person</span>
                            <input type="text" wire:model.live.debounce.300ms="customerSearch"
                                class="flex-1 bg-transparent border-none text-slate-900 dark:text-white text-sm font-medium focus:ring-0 placeholder:text-text-muted-light dark:placeholder:text-text-muted-dark"
                                placeholder="Cari Pelanggan..." @focus="open = true"
                                @blur="setTimeout(() => open = false, 200)">
                            @if($selectedCustomerId)
                                <button wire:click="selectCustomer(null)" class="text-red-500 hover:text-red-700">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            @endif
                        </div>
                        <button wire:click="openNewCustomerModal"
                            class="p-2 bg-primary/10 hover:bg-primary/20 text-primary rounded-xl transition-colors border border-primary/20"
                            title="Tambah Pelanggan Baru">
                            <span class="material-symbols-outlined">add</span>
                        </button>

                        @if(!empty($customers) && !$selectedCustomerId)
                            <div
                                class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-lg z-50 overflow-hidden max-h-60 overflow-y-auto">
                                @foreach($customers as $cust)
                                    <button wire:click="selectCustomer({{ $cust->id }})"
                                        class="w-full text-left px-4 py-3 text-sm hover:bg-slate-100 dark:hover:bg-[#382929] text-slate-900 dark:text-white border-b border-border-light dark:border-border-dark last:border-0">
                                        <p class="font-bold">{{ $cust->name }}</p>
                                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark">{{ $cust->phone }}
                                        </p>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Product Search -->
                    <label class="flex flex-col w-full h-12">
                        <div class="flex w-full flex-1 items-stretch rounded-xl h-full shadow-sm">
                            <div
                                class="text-text-muted-light dark:text-text-muted-dark flex border border-r-0 border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark items-center justify-center pl-4 rounded-l-xl">
                                <span class="material-symbols-outlined text-[24px]">search</span>
                            </div>
                            <input
                                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-r-xl text-slate-900 dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 focus:border-primary border border-l-0 border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark h-full placeholder:text-text-muted-light dark:placeholder:text-text-muted-dark px-4 text-base font-medium leading-normal transition-all"
                                placeholder="Cari produk atau scan barcode (F2)" x-model="searchQuery"
                                x-on:keydown.enter.prevent="let val = searchQuery; searchQuery = ''; addToCartByBarcode(val);"
                                x-on:clear-search.window="searchQuery = ''; $el.focus();"
                                x-on:keydown.window="if(!['INPUT','TEXTAREA','SELECT'].includes($event.target.tagName) && !$event.ctrlKey && !$event.metaKey && $event.key.length === 1) { $el.focus(); }"
                                x-init="$el.focus()" id="search-input" x-ref="searchInput" />
                        </div>
                    </label>
                </div>
                <div
                    class="flex overflow-x-auto no-scrollbar gap-8 pb-0 border-t border-border-light dark:border-border-dark pt-2">
                    <button
                        class="flex flex-col items-center justify-center border-b-[3px] pb-3 px-1 min-w-[60px] transition-all"
                        :class="!selectedCategory ? 'border-b-primary text-slate-900 dark:text-white' : 'border-b-transparent text-text-muted-light dark:text-text-muted-dark hover:text-slate-900 dark:hover:text-white hover:border-b-slate-300 dark:hover:border-b-white/20'"
                        @click="selectCategory(null)">
                        <p class="text-sm font-bold leading-normal tracking-[0.015em]">Semua</p>
                    </button>
                    <template x-for="category in categories" :key="category.id">
                        <button
                            class="flex flex-col items-center justify-center border-b-[3px] pb-3 px-1 min-w-[60px] transition-all"
                            :class="selectedCategory === category.id ? 'border-b-primary text-slate-900 dark:text-white' : 'border-b-transparent text-text-muted-light dark:text-text-muted-dark hover:text-slate-900 dark:hover:text-white hover:border-b-slate-300 dark:hover:border-b-white/20'"
                            @click="selectCategory(category.id)">
                            <p class="text-sm font-bold leading-normal tracking-[0.015em]" x-text="category.name"></p>
                        </button>
                    </template>
                </div>
            </div>
            <!-- Scrollable Grid Area -->
            <div class="flex-1 overflow-y-auto p-6 scroll-smooth">
                <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <!-- Product Card -->
                        <div class="group cursor-pointer flex flex-col gap-3 p-3 rounded-xl bg-surface-light dark:bg-surface-dark border border-transparent hover:border-primary/50 hover:bg-slate-50 dark:hover:bg-[#2a1f1f] transition-all duration-200 shadow-sm hover:shadow-lg hover:shadow-primary/5"
                            @click.stop="addToCart(product.id)">
                            <div
                                class="relative w-full aspect-square bg-slate-200 dark:bg-[#382929] rounded-lg overflow-hidden flex items-center justify-center">
                                <template x-if="product.image">
                                    <div class="absolute inset-0 bg-center bg-cover bg-no-repeat"
                                        :style="'background-image: url(' + getImageUrl(product.image) + ');'"></div>
                                </template>
                                <template x-if="!product.image">
                                    <div class="absolute inset-0 bg-center bg-cover bg-no-repeat"
                                        :style="'background-image: url({{ asset('images/placeholder.png') }});'"></div>
                                </template>
                                <div
                                    class="absolute top-2 right-2 bg-primary text-white text-xs font-bold px-2 py-1 rounded shadow-sm">
                                    Rp <span x-text="formatNumber(product.selling_price)"></span>
                                </div>
                                <template x-if="!product.unlimited_stock && product.stock <= 0">
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                                        <span
                                            class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">HABIS</span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <p class="text-slate-900 dark:text-white text-base font-bold leading-tight mb-1 group-hover:text-primary transition-colors line-clamp-2"
                                    x-text="product.name"></p>
                                <p
                                    class="text-text-muted-light dark:text-text-muted-dark text-xs font-normal leading-normal">
                                    Stok: <span x-text="product.unlimited_stock ? '∞' : product.stock"></span>
                                </p>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredProducts.length === 0">
                        <div
                            class="col-span-full flex flex-col items-center justify-center text-text-muted-light dark:text-text-muted-dark py-10">
                            <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search_off</span>
                            <p>Tidak ada produk ditemukan</p>
                        </div>
                    </template>

                    <template x-if="filteredProducts.length >= perPage">
                        <div class="col-span-full pt-4 flex justify-center">
                            <button @click="loadMore"
                                class="bg-slate-200 dark:bg-[#382929] hover:bg-slate-300 dark:hover:bg-[#4a3636] border border-slate-300 dark:border-[#533c3d] text-slate-700 dark:text-white font-bold py-3 px-8 rounded-xl flex items-center gap-2 transition-all shadow-sm">
                                <x-heroicon-o-archive-box-arrow-down class="w-5 h-5" />
                                Muat Lebih Banyak
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Mobile Cart Toggle FAB -->
            <button @click="mobileCartOpen = !mobileCartOpen"
                class="lg:hidden absolute bottom-6 right-6 size-14 bg-primary text-white rounded-full shadow-lg flex items-center justify-center z-30">
                <span class="material-symbols-outlined">shopping_cart</span>
                @if(count($cart) > 0)
                    <span
                        class="absolute -top-1 -right-1 bg-white text-primary text-xs font-bold size-5 flex items-center justify-center rounded-full border border-primary">{{ count($cart) }}</span>
                @endif
            </button>
        </section>

        <!-- Right Panel: Cart / Ticket (30%) -->
        <aside
            class="w-full lg:w-[30%] flex flex-col bg-surface-light dark:bg-surface-dark border-l border-border-light dark:border-border-dark shadow-xl z-20 absolute lg:relative inset-y-0 right-0 transition-transform duration-300 lg:translate-x-0"
            :class="mobileCartOpen ? 'translate-x-0' : 'translate-x-full'">
            <!-- Cart Header -->
            <div
                class="flex items-center justify-between px-6 py-5 border-b border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark">
                <div>
                    <h3 class="text-slate-900 dark:text-white text-xl font-bold">Keranjang</h3>
                    <p class="text-text-muted-light dark:text-text-muted-dark text-sm">#{{ rand(1000, 9999) }}
                        {{ $customer->name ?? 'Walk-in Customer' }}
                    </p>
                </div>
                <button class="lg:hidden text-text-muted-light dark:text-text-muted-dark"
                    @click="mobileCartOpen = false">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Cart Items List (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="(item, index) in cart" :key="item.product_id">
                    <!-- Cart Item -->
                    <div
                        class="flex items-center gap-4 bg-slate-100 dark:bg-[#1e1515] p-3 rounded-lg border border-transparent hover:border-border-light dark:hover:border-border-dark transition-colors group">
                        <div
                            class="bg-slate-200 dark:bg-[#382929] rounded-md shrink-0 size-14 overflow-hidden relative flex items-center justify-center">
                            <template x-if="item.image">
                                <div class="absolute inset-0 bg-center bg-cover bg-no-repeat"
                                    :style="'background-image: url(' + getImageUrl(item.image) + ');'"></div>
                            </template>
                            <template x-if="!item.image">
                                <span
                                    class="material-symbols-outlined text-text-muted-light dark:text-text-muted-dark opacity-50">inventory_2</span>
                            </template>
                        </div>
                        <div class="flex flex-col flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <p class="text-slate-900 dark:text-white text-sm font-medium leading-tight line-clamp-1"
                                    x-text="item.name"></p>
                                <div class="text-right">
                                    <template x-if="item.price < item.original_price">
                                        <p class="text-xs text-text-muted-light dark:text-text-muted-dark line-through"
                                            x-text="'Rp ' + formatNumber(item.original_price * item.quantity)"></p>
                                    </template>
                                    <p class="text-slate-900 dark:text-white text-sm font-bold"
                                        x-text="'Rp ' + formatNumber(item.total)"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="text-text-muted-light dark:text-text-muted-dark text-xs font-normal"
                                    x-text="'Rp ' + formatNumber(item.price) + ' / unit'"></p>
                                <template x-if="item.discount_info">
                                    <span
                                        class="text-[10px] font-bold px-1.5 py-0.5 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded"
                                        x-text="item.discount_info"></span>
                                </template>
                                <button @click="openItemDiscountModal(index)"
                                    class="text-xs text-primary hover:underline ml-1">Edit Diskon</button>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-2">
                                    <button
                                        class="size-6 flex items-center justify-center rounded bg-slate-200 dark:bg-[#382929] text-slate-700 dark:text-white hover:bg-primary hover:text-white transition-colors"
                                        @click="decrementQuantity(index)">
                                        <span class="material-symbols-outlined text-sm">remove</span>
                                    </button>
                                    <span class="text-slate-900 dark:text-white text-sm font-medium w-6 text-center"
                                        x-text="item.quantity"></span>
                                    <button
                                        class="size-6 flex items-center justify-center rounded bg-slate-200 dark:bg-[#382929] text-slate-700 dark:text-white hover:bg-primary hover:text-white transition-colors"
                                        @click="incrementQuantity(index)">
                                        <span class="material-symbols-outlined text-sm">add</span>
                                    </button>
                                </div>
                                <button
                                    class="text-text-muted-light dark:text-text-muted-dark hover:text-red-500 transition-opacity"
                                    @click="removeFromCart(index)">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="cart.length === 0">
                    <div
                        class="flex flex-col items-center justify-center h-full text-text-muted-light dark:text-text-muted-dark opacity-50 py-10">
                        <span class="material-symbols-outlined text-6xl mb-2">shopping_basket</span>
                        <p>Keranjang Kosong</p>
                    </div>
                </template>
            </div>
            <!-- Cart Footer / Totals -->
            <div
                class="bg-slate-100 dark:bg-[#1e1515] border-t border-border-light dark:border-border-dark p-6 space-y-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
                <div class="space-y-2">
                    <div class="flex justify-between text-text-muted-light dark:text-text-muted-dark text-sm">
                        <span>Subtotal</span>
                        <span class="text-slate-900 dark:text-white font-medium"
                            x-text="'Rp ' + formatNumber(subtotal)"></span>
                    </div>
                    <template x-if="tax > 0">
                        <div class="flex justify-between text-text-muted-light dark:text-text-muted-dark text-sm">
                            <span x-text="'Pajak (' + tax + '%)'"></span>
                            <span class="text-slate-900 dark:text-white font-medium"
                                x-text="'Rp ' + formatNumber((subtotal - discount) * (tax / 100))"></span>
                        </div>
                    </template>
                    <template x-if="discount > 0">
                        <div class="flex justify-between text-text-muted-light dark:text-text-muted-dark text-sm">
                            <span>Diskon</span>
                            <span class="text-green-500 font-medium" x-text="'-Rp ' + formatNumber(discount)"></span>
                        </div>
                    </template>
                </div>
                <div
                    class="flex justify-between items-end pt-2 border-t border-slate-200 dark:border-[#382929] border-dashed">
                    <span class="text-slate-900 dark:text-white font-medium text-lg">Total</span>
                    <span class="text-slate-900 dark:text-white font-bold text-3xl"
                        x-text="'Rp ' + formatNumber(total)"></span>
                </div>
                <template x-if="cart.length > 0">
                    <div class="grid grid-cols-[1fr_2fr] gap-3 pt-2">
                        <button
                            class="flex items-center justify-center rounded-xl bg-slate-200 dark:bg-[#382929] text-slate-700 dark:text-white hover:bg-slate-300 dark:hover:bg-[#4a3636] transition-colors py-4 text-base font-bold"
                            @click="clearCart" title="Alt+Delete">
                            <span class="material-symbols-outlined mr-2">delete</span>
                            Clear (Alt+Del)
                        </button>
                        <button
                            class="flex items-center justify-center rounded-xl bg-primary text-white hover:bg-red-600 transition-colors py-4 text-lg font-bold shadow-[0_0_15px_rgba(234,42,51,0.4)] hover:shadow-[0_0_20px_rgba(234,42,51,0.6)]"
                            @click="openCheckout()">
                            Bayar
                            <span class="material-symbols-outlined ml-2">arrow_forward</span>
                        </button>

                        <button
                            class="col-span-1 flex items-center justify-center rounded-xl bg-slate-200 dark:bg-[#382929] text-slate-700 dark:text-white hover:bg-slate-300 dark:hover:bg-[#4a3636] transition-colors py-2 text-sm font-bold"
                            @click="showGlobalDiscountModal = true">
                            <x-heroicon-o-tag class="w-5 h-5 mr-1 text-yellow-500" /> Diskon (F4)
                        </button>
                        <button
                            class="col-span-1 flex items-center justify-center rounded-xl transition-all py-2 text-sm font-bold"
                            :class="tax > 0 ? 'bg-slate-200 dark:bg-[#382929] text-slate-700 dark:text-white hover:bg-slate-300 dark:hover:bg-[#4a3636]' : 'bg-surface-light dark:bg-surface-dark border border-dashed border-text-muted-light dark:border-text-muted-dark text-text-muted-light dark:text-text-muted-dark hover:text-slate-900 dark:hover:text-white'"
                            @click="toggleTax()">
                            <template x-if="tax > 0">
                                <span class="flex items-center gap-1"><x-heroicon-o-check-circle
                                        class="w-4 h-4 text-green-500" /> Pajak On (F10)</span>
                            </template>
                            <template x-if="!(tax > 0)">
                                <span class="flex items-center gap-1"><x-heroicon-o-x-circle
                                        class="w-4 h-4 text-red-500" />
                                    Pajak Off (F10)</span>
                            </template>
                        </button>
                    </div>
                </template>
            </div>
        </aside>
    </main>

    <!-- Unified Modal Backdrop & Style -->
    <style>
        .custom-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-modal {
            border-radius: 1rem;
            width: 100%;
            /* max-width: 500px; REMOVED to allow tailwind classes */
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .dark .custom-modal {
            background: #241a1a;
            border: 1px solid #382929;
        }

        :not(.dark) .custom-modal {
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
    </style>

    <!-- Checkout Modal (Reference Design) -->
    <div class="custom-modal-backdrop" x-show="showCheckoutModal" x-cloak @click.self="showCheckoutModal = false"
        @keydown.window.escape="showCheckoutModal = false" @keydown.window.f1.prevent="setExactAmount()"
        @keydown.window.enter="if(showCheckoutModal && !['TEXTAREA', 'BUTTON'].includes($event.target.tagName)) processPayment()">
        <div class="custom-modal md:max-w-4xl w-full mx-4 rounded-3xl overflow-hidden bg-white dark:bg-slate-900 shadow-2xl flex flex-col md:flex-row max-h-[90vh] md:h-[550px]"
            @click.stop>

            <!-- Left Panel: Order Summary -->
            <div
                class="w-full md:w-5/12 bg-slate-50 dark:bg-slate-800 p-10 flex flex-col justify-between border-r border-slate-100 dark:border-slate-700 relative overflow-hidden">
                <div class="relative z-10 text-center">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Total Tagihan</p>
                    <h2 class="text-5xl font-black text-green-600 dark:text-green-500 tracking-tighter"
                        x-text="'Rp ' + formatNumber(total)"></h2>
                    <p class="text-slate-400 text-sm mt-2">Invoice #INV-{{ now()->format('Ymd') }}-001</p>
                </div>

                <div class="relative z-10 text-center py-10">
                    <div class="mb-6">
                        <span class="material-symbols-outlined text-8xl text-green-600/10">receipt_long</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm max-w-[250px] mx-auto leading-relaxed">
                        Selesaikan pembayaran untuk memproses pesanan ini.
                    </p>
                </div>

                <!-- Status Dropdown -->
                <div
                    class="relative z-10 bg-white dark:bg-slate-700 p-5 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-600">
                    <div class="flex items-center justify-between mb-2 px-1">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Pembayaran</span>
                        <span class="w-3 h-3 rounded-full" :class="{
                                 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]': paymentStatus === 'paid',
                                 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]': paymentStatus === 'unpaid',
                                 'bg-orange-500 shadow-[0_0_10px_rgba(249,115,22,0.5)]': paymentStatus === 'debt'
                             }"></span>
                    </div>
                    <select wire:model.live="paymentStatus"
                        class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-bold text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-green-500 cursor-pointer py-3 px-4">
                        <option value="paid">Lunas (Paid)</option>
                        <option value="unpaid">Belum Bayar (Unpaid)</option>
                        <option value="debt">Hutang (Debt)</option>
                    </select>
                </div>
            </div>

            <!-- Right Panel: Payment Details -->
            <div class="w-full md:w-7/12 bg-white dark:bg-slate-900 p-6 flex flex-col overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Detail Pembayaran</h3>
                        <p class="text-slate-500 text-xs">Lengkapi detail transaksi di bawah ini.</p>
                    </div>
                    <button @click="showCheckoutModal = false"
                        class="text-slate-400 hover:text-red-500 transition-colors">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Payment Status: Unpaid Blocking View -->
                <template x-if="paymentStatus === 'unpaid'">
                    <div
                        class="flex-1 flex flex-col items-center justify-center text-center space-y-4 opacity-70 p-10 border-2 border-dashed border-slate-200 rounded-3xl">
                        <span class="material-symbols-outlined text-5xl text-slate-400">money_off</span>
                        <p class="text-slate-500 font-medium">Status 'Belum Bayar' dipilih. <br>Tidak ada pembayaran
                            yang perlu diproses.</p>
                    </div>
                </template>

                <template x-if="paymentStatus !== 'unpaid'">
                    <div class="flex-1 space-y-4">
                        <!-- Amount Input -->
                        <div x-show="paymentMethod === 'cash'">
                            <label class="block text-xs font-bold text-slate-700 dark:text-gray-300 mb-1">Nominal
                                Diterima</label>
                            <div class="relative">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                                <input type="number" x-model="amountPaid" @input="calculateChange()"
                                    class="w-full pl-12 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-lg font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all shadow-sm placeholder-slate-300"
                                    placeholder="" min="0" x-ref="paymentInput" @keydown.enter="processPayment()"
                                    x-init="$watch('showCheckoutModal', value => { if (value && paymentMethod === 'cash') setTimeout(() => $el.focus(), 100) });
                                            $watch('paymentMethod', value => { if (value === 'cash' && showCheckoutModal) setTimeout(() => $el.focus(), 100) })">
                            </div>
                            <!-- Quick Amounts -->
                            <div class="flex gap-2 mt-2 overflow-x-auto pb-1 no-scrollbar">
                                <button
                                    class="px-3 py-1.5 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-200"
                                    @click="setQuickAmount(20000)">20k</button>
                                <button
                                    class="px-3 py-1.5 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-200"
                                    @click="setQuickAmount(50000)">50k</button>
                                <button
                                    class="px-3 py-1.5 bg-slate-100 rounded-lg text-[10px] font-bold text-slate-600 hover:bg-slate-200"
                                    @click="setQuickAmount(100000)">100k</button>
                                <button
                                    class="px-3 py-1.5 bg-primary/10 rounded-lg text-[10px] font-bold text-primary hover:bg-primary/20"
                                    @click="setExactAmount()">Uang Pas (F1)</button>
                            </div>

                            <!-- Kembalian Display -->
                            <template x-if="change > 0 || amountPaid >= total">
                                <div
                                    class="mt-2 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800 flex justify-between items-center animate-in fade-in slide-in-from-top-2">
                                    <span
                                        class="text-emerald-800 dark:text-emerald-300 font-bold flex items-center gap-2 text-xs">
                                        <span class="material-symbols-outlined text-lg">payments</span>
                                        Kembalian
                                    </span>
                                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400"
                                        x-text="'Rp ' + formatNumber(change)"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-gray-300 mb-2">Metode
                                Pembayaran</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    class="relative p-2 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 h-20"
                                    :class="paymentMethod === 'cash' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 hover:border-slate-300 text-slate-500'"
                                    @click="setPaymentMethod('cash')">
                                    <span class="material-symbols-outlined text-xl">payments</span>
                                    <span class="font-bold text-xs">Cash</span>
                                    <div x-show="paymentMethod === 'cash'"
                                        class="absolute top-1 right-1 bg-primary text-white rounded-full p-0.5">
                                        <span class="material-symbols-outlined text-[8px] block">check</span>
                                    </div>
                                </button>
                                <button
                                    class="relative p-2 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 h-20"
                                    :class="paymentMethod === 'qris' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 hover:border-slate-300 text-slate-500'"
                                    @click="setPaymentMethod('qris')">
                                    <span class="material-symbols-outlined text-xl">qr_code_scanner</span>
                                    <span class="font-bold text-xs">QRIS</span>
                                    <div x-show="paymentMethod === 'qris'"
                                        class="absolute top-1 right-1 bg-primary text-white rounded-full p-0.5">
                                        <span class="material-symbols-outlined text-[8px] block">check</span>
                                    </div>
                                </button>
                                <button
                                    class="relative p-2 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 h-20"
                                    :class="paymentMethod === 'transfer' ? 'border-primary bg-primary/5 text-primary' : 'border-slate-100 hover:border-slate-300 text-slate-500'"
                                    @click="setPaymentMethod('transfer')">
                                    <span class="material-symbols-outlined text-xl">account_balance</span>
                                    <span class="font-bold text-xs">Transfer</span>
                                    <div x-show="paymentMethod === 'transfer'"
                                        class="absolute top-1 right-1 bg-primary text-white rounded-full p-0.5">
                                        <span class="material-symbols-outlined text-[8px] block">check</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-700 dark:text-gray-300 mb-1">Catatan</label>
                            <textarea wire:model="note"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-slate-700 text-xs focus:ring-2 focus:ring-primary focus:border-transparent transition-all shadow-sm placeholder-slate-300 resize-none h-16"
                                placeholder="Tambah catatan..."></textarea>
                        </div>
                    </div>
                </template>

                <!-- Footer Button -->
                <div class="mt-auto pt-4">
                    <button
                        class="w-full py-3 rounded-xl font-bold text-base shadow-lg hover:shadow-xl hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2"
                        :class="(paymentStatus === 'paid' && paymentMethod === 'cash' && amountPaid < total) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-red-600 text-white hover:bg-red-700 shadow-red-600/30'"
                        :disabled="(paymentStatus === 'paid' && paymentMethod === 'cash' && amountPaid < total)"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait"
                        @click="processPayment()">
                        <span wire:loading.remove>Proses Pembayaran (ENTER)</span>
                        <span wire:loading>Memproses...</span>
                        <span wire:loading.remove class="material-symbols-outlined text-xl">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Discount Modal (Alpine) -->
    <div class="custom-modal-backdrop" x-show="showGlobalDiscountModal" x-cloak
        @click.self="showGlobalDiscountModal = false" @keydown.window.escape="showGlobalDiscountModal = false"
        @keydown.window.f1.prevent="if(showGlobalDiscountModal) globalDiscountType = 0"
        @keydown.window.f2.prevent="if(showGlobalDiscountModal) globalDiscountType = 1">
        <div class="custom-modal" @click.stop style="max-width: 400px;">
            <div class="p-5 border-b border-gray-200 bg-slate-50">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-6 h-6 text-yellow-500" /> Tambah Diskon
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="flex bg-slate-200 p-1 rounded-lg">
                    <button class="flex-1 py-2 rounded-md text-sm font-bold transition-all"
                        :class="globalDiscountType == 0 ? 'bg-primary text-white shadow' : 'text-gray-600'"
                        @click="globalDiscountType = 0">
                        Nominal (Rp) (F1)
                    </button>
                    <button class="flex-1 py-2 rounded-md text-sm font-bold transition-all"
                        :class="globalDiscountType == 1 ? 'bg-primary text-white shadow' : 'text-gray-600'"
                        @click="globalDiscountType = 1">
                        Persen (%) (F2)
                    </button>
                </div>

                <input type="number" x-model="globalDiscountValue"
                    class="w-full bg-white border border-gray-300 rounded-lg p-3 text-slate-900 text-xl font-bold focus:ring-2 focus:ring-primary focus:border-transparent text-center"
                    placeholder="0" @keydown.enter="applyGlobalDiscount()"
                    x-init="$watch('showGlobalDiscountModal', value => value && $nextTick(() => $el.focus()))">
            </div>
            <div class="p-5 border-t border-gray-200 flex gap-3 bg-slate-50">
                <button
                    class="flex-1 py-3 px-4 rounded-xl border border-gray-300 text-slate-700 hover:bg-slate-100 transition-colors font-bold"
                    @click="showGlobalDiscountModal = false">
                    Batal
                </button>
                <button
                    class="flex-1 py-3 px-4 rounded-xl bg-primary text-white hover:bg-red-600 transition-colors font-bold"
                    @click="applyGlobalDiscount()">
                    Terapkan (Enter)
                </button>
            </div>
        </div>
    </div>

    <!-- Item Discount Modal (Alpine) -->
    <div class="custom-modal-backdrop" x-show="showItemDiscountModal" x-cloak
        @click.self="showItemDiscountModal = false" @keydown.window.escape="showItemDiscountModal = false"
        @keydown.window.f1.prevent="if(showItemDiscountModal) itemDiscountType = 0"
        @keydown.window.f2.prevent="if(showItemDiscountModal) itemDiscountType = 1">
        <div class="custom-modal" @click.stop style="max-width: 400px;">
            <div class="p-5 border-b border-gray-200 bg-slate-50">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <x-heroicon-o-tag class="w-6 h-6 text-yellow-500" /> Diskon Item
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Toggle Type -->
                <div class="flex bg-slate-200 p-1 rounded-lg">
                    <button class="flex-1 py-2 rounded-md text-sm font-bold transition-all"
                        :class="itemDiscountType == 0 ? 'bg-primary text-white shadow' : 'text-gray-600'"
                        @click="itemDiscountType = 0">
                        Nominal (Rp) (F1)
                    </button>
                    <button class="flex-1 py-2 rounded-md text-sm font-bold transition-all"
                        :class="itemDiscountType == 1 ? 'bg-primary text-white shadow' : 'text-gray-600'"
                        @click="itemDiscountType = 1">
                        Persen (%) (F2)
                    </button>
                </div>

                <input type="number" x-model="itemDiscountValue"
                    class="w-full bg-white border border-gray-300 rounded-lg p-3 text-slate-900 text-xl font-bold focus:ring-2 focus:ring-primary focus:border-transparent text-center"
                    placeholder="0" @keydown.enter="applyItemDiscount()"
                    x-init="$watch('showItemDiscountModal', value => value && $nextTick(() => $el.focus()))">
                <p class="text-xs text-center text-gray-600">
                    Kosongkan atau isi 0 untuk menghapus diskon manual.
                </p>
            </div>
            <div class="p-5 border-t border-gray-200 flex gap-3 bg-slate-50">
                <button
                    class="flex-1 py-3 px-4 rounded-xl border border-gray-300 text-slate-700 hover:bg-slate-100 transition-colors font-bold"
                    @click="showItemDiscountModal = false">
                    Batal
                </button>
                <button
                    class="flex-1 py-3 px-4 rounded-xl bg-primary text-white hover:bg-red-600 transition-colors font-bold"
                    @click="applyItemDiscount()">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    @if($showProfileModal)
        <div class="custom-modal-backdrop" wire:click.self="closeModal">
            <div class="custom-modal" wire:click.stop>
                <div
                    class="p-5 border-b border-border-light dark:border-border-dark bg-slate-50 dark:bg-background-dark/50">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2"><x-heroicon-o-user
                            class="w-6 h-6 text-primary" /> Pengaturan Profil</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-text-muted-light dark:text-text-muted-dark text-sm font-bold">Nama
                            Lengkap</label>
                        <input type="text" wire:model="profileName"
                            class="w-full bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg p-2.5 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                        @error('profileName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-text-muted-light dark:text-text-muted-dark text-sm font-bold">Email</label>
                        <input type="email" wire:model="profileEmail"
                            class="w-full bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg p-2.5 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                        @error('profileEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 mt-4 border-t border-border-light dark:border-border-dark">
                        <p class="text-text-muted-light dark:text-text-muted-dark text-xs mb-3 italic">Kosongkan jika tidak
                            ingin mengubah password</p>
                        <div class="space-y-3">
                            <div class="space-y-2">
                                <label class="text-text-muted-light dark:text-text-muted-dark text-sm font-bold">Password
                                    Baru</label>
                                <input type="password" wire:model="profilePassword"
                                    class="w-full bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg p-2.5 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                                @error('profilePassword') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-text-muted-light dark:text-text-muted-dark text-sm font-bold">Konfirmasi
                                    Password</label>
                                <input type="password" wire:model="profilePasswordConfirmation"
                                    class="w-full bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-lg p-2.5 text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="p-5 border-t border-border-light dark:border-border-dark flex gap-3 bg-slate-50 dark:bg-background-dark/50">
                    <button
                        class="flex-1 py-3 px-4 rounded-xl border border-border-light dark:border-border-dark text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-[#382929] transition-colors font-bold"
                        wire:click="closeModal">Batal</button>
                    <button
                        class="flex-1 py-3 px-4 rounded-xl bg-primary text-white hover:bg-red-600 transition-colors font-bold"
                        wire:click="updateProfile">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- New Customer Modal -->
    @if($showNewCustomerModal)
        <div class="custom-modal-backdrop" wire:click.self="closeModal">
            <div class="custom-modal md:max-w-md w-full mx-4 bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden"
                wire:click.stop>
                <div class="p-5 border-b border-gray-200 dark:border-gray-700 bg-slate-50 dark:bg-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">person_add</span>
                        Tambah Pelanggan Baru
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-gray-300 mb-1">Nama Lengkap*</label>
                        <input type="text" wire:model="newCustomerName" placeholder="Nama Pelanggan"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        @error('newCustomerName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-gray-300 mb-1">Nomor Telepon*</label>
                        <input type="text" wire:model="newCustomerPhone" placeholder="08..."
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        @error('newCustomerPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-gray-300 mb-1">Email
                            (Opsional)</label>
                        <input type="email" wire:model="newCustomerEmail" placeholder="email@contoh.com"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        @error('newCustomerEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-gray-300 mb-1">Alamat
                            (Opsional)</label>
                        <textarea wire:model="newCustomerAddress" rows="2" placeholder="Alamat lengkap"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary resize-none"></textarea>
                    </div>
                </div>
                <div
                    class="p-5 border-t border-gray-200 dark:border-gray-700 bg-slate-50 dark:bg-slate-800 flex justify-end gap-3">
                    <button wire:click="closeModal"
                        class="px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-xl text-slate-700 dark:text-gray-200 font-bold hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                        Batal
                    </button>
                    <button wire:click="createCustomer"
                        class="px-4 py-2 bg-primary text-white rounded-xl font-bold hover:bg-red-600 transition-colors shadow-lg shadow-primary/30">
                        Simpan Pelanggan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Notifications -->
    <div x-data="{ notifications: [] }"
        @notify.window="notifications.push($event.detail); setTimeout(() => { notifications.shift() }, 3000)"
        class="fixed top-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none">
        <template x-for="note in notifications">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-10"
                class="pointer-events-auto bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-slate-900 dark:text-white p-4 rounded-xl shadow-2xl flex items-center gap-3 min-w-[300px]"
                :class="{ 'border-l-4 border-l-primary': note.type === 'success', 'border-l-red-500': note.type === 'error' }">

                <span class="material-symbols-outlined text-green-500"
                    x-show="note.type === 'success'">check_circle</span>
                <span class="material-symbols-outlined text-red-500" x-show="note.type === 'error'">error</span>

                <div>
                    <p class="font-bold text-sm" x-text="note.type === 'success' ? 'Sukses' : 'Error'"></p>
                    <p class="text-xs text-text-muted-light dark:text-text-muted-dark" x-text="note.message"></p>
                </div>
            </div>
        </template>
    </div>

    <script>
        // Inject Printer Type
        window.posPrinterType = @json($printerType);

        // Global Printer Instance
        let printerInstance = null;
        window.btPrinter = null;

        function connectPrinter() {
            console.log("Connect button clicked");
            if (typeof PrintHub === 'undefined') {
                console.error("PrintHub library not loaded");
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Library PrintHub belum siap. Coba refresh halaman.' } }));
                return;
            }

            // Determine printer type from Blade variable (passed as global or checked here)
            // Since we can't easily access PHP $printerType directly in JS function without passing it, 
            // we will rely on checking the button or assuming the user knows what they are connecting.
            // Better: Pass printer type to this function? Or read from a global var.
            // Let's assume we initialize the type correctly based on the settings.

            // For now, let's try to detect based on global setting injected or just try default.
            // But wait, PrintHub init needs type.
            // Let's inject the type from Blade into a global JS variable.

            const pType = window.posPrinterType || 'bluetooth';

            if (!printerInstance) {
                try {
                    // PrintHub Init
                    printerInstance = new PrintHub.init({
                        paperSize: "58",
                        printerType: pType === 'usb_web' ? 'usb' : 'bluetooth'
                    });
                    console.log("PrintHub instance created for " + pType);
                } catch (e) {
                    console.error("Error creating printer instance:", e);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Error init printer: ' + e.message } }));
                    return;
                }
            }

            printerInstance.connectToPrint({
                onReady: (print) => {
                    window.btPrinter = print;
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Printer Terhubung (' + (pType === 'usb_web' ? 'USB' : 'Bluetooth') + ')!' } }));
                    console.log("Printer Connected Successfully", print);
                },
                onFailed: (message) => {
                    let errorBody = message;

                    // Specific guidance for claimInterface error on USB
                    if (pType === 'usb_web' && message.includes('claimInterface')) {
                        errorBody = 'Gagal claim interface. Pastikan driver printer sudah diganti ke WinUSB menggunakan Zadig.';
                    }

                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Gagal Konek: ' + errorBody } }));
                    console.error("Connection Failed:", message);
                }
            });
        }

        async function autoConnectPrinter() {
            const pType = window.posPrinterType || 'bluetooth';
            console.log("Printer auto-connect: " + pType);
            if (typeof PrintHub === 'undefined') {
                console.warn("PrintHub not defined during autoConnectPrinter");
                return;
            }

            // USB Auto-Connect
            if (pType === 'usb_web' && navigator.usb) {
                try {
                    const devices = await navigator.usb.getDevices();
                    if (devices.length > 0) {
                        console.log("USB device found, connecting...");

                        const originalRequest = navigator.usb.requestDevice;
                        // Use a more robust patch that specifically returns the first known device
                        navigator.usb.requestDevice = function () { return Promise.resolve(devices[0]); };

                        try {
                            if (!printerInstance) {
                                printerInstance = new PrintHub.init({
                                    paperSize: "58",
                                    printerType: 'usb'
                                });
                            }

                            printerInstance.connectToPrint({
                                onReady: (print) => {
                                    window.btPrinter = print;
                                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Printer USB Terhubung Otomatis!' } }));
                                    navigator.usb.requestDevice = originalRequest;
                                },
                                onFailed: (message) => {
                                    console.warn("Auto-connect logic failed inside PrintHub (USB):", message);
                                    // Don't show scary error if it's just a permission/gesture issue during auto-recon
                                    if (message.includes('SecurityError') || message.includes('Access Denied') || message.includes('Must be handling a user gesture')) {
                                        console.log("Auto-connect blocked by browser security (User Gesture required).");
                                        window.dispatchEvent(new CustomEvent('printer-gesture-required'));
                                    } else {
                                        // Still notify for other errors like device busy/disconnected
                                        // window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Gagal Konek USB: ' + message } }));
                                    }
                                    navigator.usb.requestDevice = originalRequest;
                                }
                            });
                        } catch (err) {
                            console.error("Error during auto-connect patch (USB):", err);
                            navigator.usb.requestDevice = originalRequest;
                        }
                    }
                } catch (e) {
                    console.error("Auto-disconnect check USB failed:", e);
                }
            }

            // Bluetooth Auto-Connect
            if (pType === 'bluetooth' && navigator.bluetooth && navigator.bluetooth.getDevices) {
                try {
                    const devices = await navigator.bluetooth.getDevices();
                    if (devices.length > 0) {
                        console.log("Bluetooth device found: " + (devices[0].name || "Unknown"));

                        // Monkey-patch requestDevice for Bluetooth
                        const originalRequest = navigator.bluetooth.requestDevice;
                        navigator.bluetooth.requestDevice = function () { return Promise.resolve(devices[0]); };

                        try {
                            if (!printerInstance) {
                                printerInstance = new PrintHub.init({
                                    paperSize: "58",
                                    printerType: 'bluetooth'
                                });
                            }

                            printerInstance.connectToPrint({
                                onReady: (print) => {
                                    window.btPrinter = print;
                                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Printer Bluetooth Terhubung Otomatis!' } }));
                                    navigator.bluetooth.requestDevice = originalRequest;
                                },
                                onFailed: (message) => {
                                    console.warn("Auto-connect logic failed inside PrintHub (BT):", message);
                                    if (message.includes('SecurityError') || message.includes('Access Denied') || message.includes('Must be handling a user gesture') || message.includes('no longer in range')) {
                                        console.log("BT Auto-connect blocked by browser security (User Gesture required).");
                                        // Dispatch a quiet event that the UI can use to show a "Click to connect" hint
                                        window.dispatchEvent(new CustomEvent('printer-gesture-required'));
                                    }
                                    navigator.bluetooth.requestDevice = originalRequest;
                                }
                            });
                        } catch (err) {
                            console.error("Error during auto-connect patch (BT):", err);
                            navigator.bluetooth.requestDevice = originalRequest;
                        }
                    }
                } catch (e) {
                    console.error("Auto-disconnect check Bluetooth failed:", e);
                }
            }
        }

        document.addEventListener('livewire:init', () => {
            // Attempt Auto-Connect
            setTimeout(autoConnectPrinter, 1000); // Small delay to ensure lib loaded

            // Livewire.on('printReceipt', (data) => {
            //     let iframe = document.getElementById('receipt-frame');
            //     if (!iframe) {
            //         iframe = document.createElement('iframe');
            //         iframe.id = 'receipt-frame';
            //         iframe.style.position = 'absolute';
            //         iframe.style.width = '0px';
            //         iframe.style.height = '0px';
            //         iframe.style.border = 'none';
            //         document.body.appendChild(iframe);
            //     }
            //     iframe.src = '/pos/receipt/' + data.orderId;
            // });

            Livewire.on('printBluetoothReceipt', async (data) => {
                // data might be wrapped in an array [data] depending on Livewire version/dispatch
                // If dispatched as named param "data", it's usually the first arg.
                let receipt = data.data || data; // handle potential wrapper

                if (!window.btPrinter) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Printer Belum Terhubung! Klik tombol Bluetooth diatas.' } }));
                    return;
                }

                let print = window.btPrinter;

                try {
                    // Header
                    await print.writeText(receipt.storeName, { align: "center", bold: true, size: "double" });
                    if (receipt.storeAddress) await print.writeText(receipt.storeAddress, { align: "center" });
                    if (receipt.storePhone) await print.writeText(receipt.storePhone, { align: "center" });

                    await print.writeLineBreak();
                    await print.writeText("No: " + receipt.invoice, { align: "left" });
                    await print.writeText("Tgl: " + receipt.date, { align: "left" });
                    // await print.writeText("Kasir: " + receipt.cashier, { align: "left" });
                    // await print.writeText("Pelanggan: " + receipt.customer, { align: "left" });
                    await print.writeDashLine();

                    // Items
                    for (let item of receipt.items) {
                        await print.writeText(item.name, { align: "left" });
                        if (item.discount_info) {
                            await print.writeText("  (Disc: " + item.discount_info + ")", { align: "left" });
                        }
                        // Format: 2x @10.000   20.000
                        let line2_left = item.qty + "x @" + new Intl.NumberFormat('id-ID').format(item.price);
                        let line2_right = new Intl.NumberFormat('id-ID').format(item.total);
                        await print.writeTextWith2Column(line2_left, line2_right);
                    }
                    await print.writeDashLine();

                    // Totals
                    await print.writeTextWith2Column("Subtotal", new Intl.NumberFormat('id-ID').format(receipt.subtotal));
                    if (receipt.discount > 0) {
                        await print.writeTextWith2Column("Diskon", "-" + new Intl.NumberFormat('id-ID').format(receipt.discount));
                    }
                    if (receipt.tax > 0) {
                        await print.writeTextWith2Column("Pajak", new Intl.NumberFormat('id-ID').format(receipt.tax));
                    }

                    // Total Large
                    await print.writeLineBreak();
                    await print.writeText("TOTAL", { align: "center", bold: true });
                    await print.writeText("Rp " + new Intl.NumberFormat('id-ID').format(receipt.total), { align: "center", bold: true, size: "double" });
                    await print.writeLineBreak();

                    await print.writeTextWith2Column("Tunai", new Intl.NumberFormat('id-ID').format(receipt.amount_paid));
                    await print.writeTextWith2Column("Kembali", new Intl.NumberFormat('id-ID').format(receipt.change));

                    // Footer
                    await print.writeDashLine();
                    await print.writeText(receipt.footer, { align: "center" });
                    await print.writeLineBreak({ count: 3 }); // Feed
                    // await print.writeLineBreak();

                } catch (e) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Gagal Print: ' + e.message } }));
                    // Reset printer if connection lost?
                    // window.btPrinter = null; 
                }
            });

            Livewire.on('triggerDirectPrint', (data) => {
                fetch(`/pos/receipt/${data.orderId}/print`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: { type: 'success', message: result.message }
                            }));
                        } else {
                            throw new Error(result.message);
                        }
                    })
                    .catch(error => {
                        console.error('Print Error:', error);
                        // Fallback to browser print logic (calling the existing printReceipt handler essentially)
                        // Since we can't easily emit back to self, we just replicate the logic or dispatch event to window

                        // Dispatch internal event or just run logic
                        let iframe = document.getElementById('receipt-frame');
                        if (!iframe) {
                            iframe = document.createElement('iframe');
                            iframe.id = 'receipt-frame';
                            iframe.style.position = 'absolute';
                            iframe.style.width = '0px';
                            iframe.style.height = '0px';
                            iframe.style.border = 'none';
                            document.body.appendChild(iframe);
                        }
                        iframe.src = '/pos/receipt/' + data.orderId;

                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: { type: 'warning', message: 'Direct Print Gagal' }
                        }));
                    });
            });

            // Keyboard Shortcuts (Now handled by Alpine.js init)
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('posTerminal', () => ({
                products: @json($productsJson),
                categories: @json($categoriesJson),
                cart: [],
                searchQuery: '',
                selectedCategory: null,
                perPage: 30, // For lazy loading simulation

                subtotal: 0,
                total: 0,
                tax: @js($tax),
                defaultTax: @js($defaultTax),
                discount: 0,

                showItemDiscountModal: false,
                editingItemIndex: null,
                itemDiscountType: 0, // 0: Nominal, 1: Percent
                itemDiscountValue: 0,

                showGlobalDiscountModal: false,
                globalDiscountType: @entangle('discountType'), // 0: Nominal, 1: Percent
                globalDiscountValue: @entangle('discountValue'),

                showCheckoutModal: false,
                paymentMethod: 'cash',
                paymentStatus: @entangle('paymentStatus'),
                amountPaid: '',
                change: 0,

                printerGestureRequired: false,

                init() {
                    this.$watch('cart', () => this.calculateTotals());
                    window.addEventListener('clear-alpine-cart', () => this.clearCart());
                    window.addEventListener('refresh-products', (e) => {
                        this.products = e.detail.products || e.detail;
                    });

                    // Unified Global Keyboard Shortcuts
                    window.addEventListener('keydown', (e) => this.handleShortcuts(e));

                    // Listen for Livewire updates to products if necessary (e.g. stock updates after checkout)
                    this.$watch('products', () => console.log('Products updated'));

                    window.addEventListener('printer-gesture-required', () => {
                        this.printerGestureRequired = true;
                    });
                },

                handleShortcuts(e) {
                    // Ignore global shortcuts if any modal is open
                    if (this.showGlobalDiscountModal || this.showItemDiscountModal || this.showCheckoutModal) {
                        return;
                    }

                    if (e.key === 'F2') {
                        e.preventDefault();
                        this.$nextTick(() => {
                            if (this.$refs.searchInput) {
                                this.$refs.searchInput.focus();
                                this.$refs.searchInput.select();
                            } else {
                                document.getElementById('search-input')?.focus();
                            }
                        });
                    }
                    if (e.key === 'F4') {
                        e.preventDefault();
                        this.showGlobalDiscountModal = true;
                    }
                    if (e.key === 'F9') {
                        e.preventDefault();
                        this.openCheckout();
                    }
                    if (e.key === 'F10') {
                        e.preventDefault();
                        this.toggleTax();
                    }
                    if (e.altKey && e.key === 'Delete') {
                        e.preventDefault();
                        this.clearCart();
                    }
                },

                get filteredProducts() {
                    let filtered = this.products;

                    if (this.selectedCategory) {
                        filtered = filtered.filter(p => p.category_id === this.selectedCategory);
                    }

                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(p =>
                            p.name.toLowerCase().includes(q) ||
                            (p.barcode && p.barcode.toLowerCase().includes(q))
                        );
                    }

                    return filtered.slice(0, this.perPage);
                },

                selectCategory(id) {
                    this.selectedCategory = (this.selectedCategory === id) ? null : id;
                    this.perPage = 30; // Reset pagination on category change
                },

                loadMore() {
                    this.perPage += 30;
                },

                addToCart(productId) {
                    const product = this.products.find(p => p.id === productId);
                    if (!product) return;

                    if (!product.unlimited_stock && product.stock <= 0) {
                        this.notify('error', 'Stok produk habis');
                        return;
                    }

                    const cartItem = this.cart.find(item => item.product_id === productId);
                    if (cartItem) {
                        if (!product.unlimited_stock && cartItem.quantity >= product.stock) {
                            this.notify('warning', 'Stok tidak mencukupi');
                            return;
                        }
                        cartItem.quantity++;
                        cartItem.total = cartItem.quantity * cartItem.price;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            price: parseFloat(product.selling_price),
                            original_price: parseFloat(product.selling_price),
                            quantity: 1,
                            total: parseFloat(product.selling_price),
                            image: product.image,
                            stock: product.stock,
                            unlimited_stock: product.unlimited_stock,
                            discount_info: null,
                            manual_discount: false
                        });
                    }
                    this.notify('success', product.name + ' ditambahkan');
                },

                incrementQuantity(index) {
                    const item = this.cart[index];
                    if (item.unlimited_stock || item.quantity < item.stock) {
                        item.quantity++;
                        item.total = item.quantity * item.price;
                    } else {
                        this.notify('warning', 'Stok tidak mencukupi');
                    }
                },

                decrementQuantity(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                        this.cart[index].total = this.cart[index].quantity * this.cart[index].price;
                    } else {
                        this.removeFromCart(index);
                    }
                },

                removeFromCart(index) {
                    const name = this.cart[index].name;
                    this.cart.splice(index, 1);
                    this.notify('info', name + ' dihapus');
                },

                clearCart() {
                    this.cart = [];
                    this.globalDiscountValue = 0;
                    this.globalDiscountType = 0;
                    this.calculateTotals();
                    @this.call('clearCart');
                    //this.notify('info', 'Keranjang dikosongkan');
                },

                addToCartByBarcode(barcode) {
                    const product = this.products.find(p => p.barcode === barcode);
                    if (product) {
                        this.addToCart(product.id);
                        this.searchQuery = ''; // Clear search in Alpine
                    } else {
                        // If it's not a barcode, it might be a partial name from the input
                        // But enter usually means barcode scan in POS
                        this.notify('error', 'Produk tidak ditemukan: ' + barcode);
                    }
                },

                openCheckout() {
                    if (this.cart.length === 0) {
                        this.notify('warning', 'Keranjang masih kosong');
                        return;
                    }
                    this.amountPaid = '';
                    this.change = 0;
                    this.paymentMethod = 'cash';
                    this.paymentStatus = 'paid';
                    this.showCheckoutModal = true;
                },

                setPaymentMethod(method) {
                    this.paymentMethod = method;
                },

                setQuickAmount(amt) {
                    this.amountPaid = amt;
                    this.calculateChange();
                },

                setExactAmount() {
                    this.amountPaid = this.total;
                    this.calculateChange();
                },

                calculateChange() {
                    const paid = parseFloat(this.amountPaid) || 0;
                    this.change = Math.max(0, paid - this.total);
                },

                processPayment() {
                    if (this.paymentStatus === 'paid' && this.paymentMethod === 'cash' && this.amountPaid < this.total) {
                        this.notify('error', 'Pembayaran kurang');
                        return;
                    }

                    const paymentData = {
                        discountValue: this.globalDiscountValue,
                        discountType: this.globalDiscountType,
                        tax: this.tax,
                        paymentMethod: this.paymentMethod,
                        amountPaid: this.amountPaid,
                        paymentStatus: this.paymentStatus,
                        note: this.note
                    };

                    // Trigger the Livewire processPayment method with explicit data to avoid race conditions
                    @this.call('processPayment', this.cart, paymentData);

                    this.showCheckoutModal = false;
                },
                // Pre-item discount display is back. Now I'm preparing the global discount migration.
                openItemDiscountModal(index) {
                    this.editingItemIndex = index;
                    const item = this.cart[index];
                    this.itemDiscountType = item.itemDiscountType || 0;
                    this.itemDiscountValue = item.itemDiscountValue || 0;
                    this.showItemDiscountModal = true;
                },

                applyItemDiscount() {
                    const index = this.editingItemIndex;
                    const item = this.cart[index];
                    const val = parseFloat(this.itemDiscountValue) || 0;

                    item.manual_discount = true;
                    item.itemDiscountType = this.itemDiscountType;
                    item.itemDiscountValue = val;

                    let newPrice = item.original_price;
                    if (val > 0) {
                        if (this.itemDiscountType == 1) { // Percent
                            newPrice = item.original_price * (1 - (val / 100));
                            item.discount_info = val + '%';
                        } else { // Nominal
                            newPrice = Math.max(0, item.original_price - val);
                            item.discount_info = 'Rp ' + this.formatNumber(val);
                        }
                    } else {
                        item.discount_info = null;
                    }

                    item.price = newPrice;
                    item.total = item.quantity * item.price;

                    this.showItemDiscountModal = false;
                    this.calculateTotals();
                    this.notify('success', 'Diskon item diterapkan');
                },

                applyGlobalDiscount() {
                    const val = parseFloat(this.globalDiscountValue) || 0;
                    this.discount = 0;

                    if (val > 0) {
                        if (this.globalDiscountType == 1) { // Percent
                            this.discount = this.subtotal * (val / 100);
                        } else { // Nominal
                            this.discount = val;
                        }
                    }

                    this.showGlobalDiscountModal = false;
                    this.calculateTotals();
                    this.notify('success', 'Diskon global diterapkan');
                },

                toggleTax() {
                    if (this.tax > 0) {
                        this.tax = 0;
                        this.notify('info', 'Pajak dinonaktifkan');
                    } else {
                        this.tax = this.defaultTax;
                        this.notify('success', 'Pajak diaktifkan (' + this.tax + '%)');
                    }
                    this.calculateTotals();
                },

                calculateTotals() {
                    this.subtotal = this.cart.reduce((sum, item) => sum + item.total, 0);

                    // If cart is empty, reset discount state
                    if (this.subtotal === 0) {
                        this.globalDiscountValue = 0;
                        this.globalDiscountType = 0;
                        this.discount = 0;
                    }

                    // Simple global discount calculation
                    if (this.subtotal > 0) {
                        this.discount = 0;
                        const val = parseFloat(this.globalDiscountValue) || 0;
                        if (val > 0) {
                            if (this.globalDiscountType == 1) { // Percent
                                this.discount = this.subtotal * (val / 100);
                            } else { // Nominal
                                this.discount = Math.min(this.subtotal, val);
                            }
                        }
                    }

                    // Calculate total with tax (In sync with Livewire logic)
                    const afterDiscount = Math.max(0, this.subtotal - this.discount);
                    const taxAmount = (this.tax > 0) ? (afterDiscount * (this.tax / 100)) : 0;
                    this.total = afterDiscount + taxAmount;
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num);
                },

                notify(type, message) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type, message } }));
                },

                getImageUrl(path) {
                    if (!path) return "{{ asset('images/placeholder.png') }}";
                    return "/storage/" + path;
                }
            }));
        });
    </script>
</div>