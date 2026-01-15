<div class="min-h-screen grid lg:grid-cols-2 bg-background-dark">
    <!-- Left Section: Visuals & Branding -->
    <div class="hidden lg:relative lg:flex lg:flex-col lg:items-center lg:justify-center overflow-hidden bg-[#181111]">
        <!-- Background Effects -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-[#181111] to-[#181111] z-0"></div>
        <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 z-0 mix-blend-overlay"></div>
        <div class="absolute -top-24 -left-24 size-96 bg-primary/20 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 size-[500px] bg-red-600/10 rounded-full blur-[120px] pointer-events-none"></div>

        <!-- Content -->
        <div class="relative z-10 p-12 text-center max-w-lg">
            <div class="mx-auto size-24 bg-gradient-to-tr from-primary to-orange-500 rounded-3xl flex items-center justify-center shadow-[0_0_40px_rgba(234,42,51,0.4)] mb-8 transform rotate-6 hover:rotate-12 transition-transform duration-500">
                <span class="material-symbols-outlined text-5xl text-white">point_of_sale</span>
            </div>
            <h1 class="text-5xl font-bold text-white mb-6 tracking-tight leading-tight">
                Nabila Store <span class="text-primary">POS</span>
            </h1>
            <p class="text-xl text-[#b89d9f] leading-relaxed">
                Platform kasir modern untuk bisnis yang ingin melaju lebih cepat. Kelola penjualan, stok, dan pelanggan dalam satu tempat.
            </p>
            
            <div class="mt-12 flex items-center justify-center gap-4 text-sm font-medium text-[#b89d9f]/60">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">verified_user</span>
                    <span>Secure</span>
                </div>
                <div class="w-1 h-1 rounded-full bg-[#b89d9f]/40"></div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">bolt</span>
                    <span>Fast</span>
                </div>
                <div class="w-1 h-1 rounded-full bg-[#b89d9f]/40"></div>
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">sync</span>
                    <span>Realtime</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="flex items-center justify-center p-6 lg:p-12 relative">
        <div class="w-full max-w-md space-y-8">
            <!-- Mobile Header to match desktop feel on small screens -->
            <div class="text-center lg:text-left mb-10">
                 <div class="lg:hidden mx-auto size-14 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6 border border-primary/20 shadow-[0_0_15px_rgba(234,42,51,0.2)]">
                    <span class="material-symbols-outlined text-3xl">point_of_sale</span>
                </div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Selamat Datang</h2>
                <p class="mt-3 text-[#b89d9f]">Silakan masuk ke akun kasir Anda</p>
            </div>

            <form wire:submit="login" class="space-y-6">
                <div class="space-y-2 group">
                    <label class="text-xs font-bold text-[#b89d9f] uppercase tracking-wider group-focus-within:text-primary transition-colors" for="email">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-[#b89d9f] group-focus-within:text-white transition-colors material-symbols-outlined text-xl">mail</span>
                        <input 
                            type="email" 
                            id="email" 
                            wire:model="email"
                            class="w-full bg-surface-dark/50 border border-border-dark rounded-xl py-3.5 pl-12 pr-4 text-white placeholder:text-[#b89d9f]/30 focus:border-primary focus:ring-1 focus:ring-primary transition-all shadow-sm" 
                            placeholder="nama@toko.com"
                            autofocus
                        >
                    </div>
                    @error('email') <span class="text-red-500 text-xs font-medium pl-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2 group">
                    <label class="text-xs font-bold text-[#b89d9f] uppercase tracking-wider group-focus-within:text-primary transition-colors" for="password">Password</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-[#b89d9f] group-focus-within:text-white transition-colors material-symbols-outlined text-xl">lock</span>
                        <input 
                            type="password" 
                            id="password" 
                            wire:model="password"
                            class="w-full bg-surface-dark/50 border border-border-dark rounded-xl py-3.5 pl-12 pr-4 text-white placeholder:text-[#b89d9f]/30 focus:border-primary focus:ring-1 focus:ring-primary transition-all shadow-sm" 
                            placeholder="••••••••"
                        >
                    </div>
                    @error('password') <span class="text-red-500 text-xs font-medium pl-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" wire:model="remember" class="peer h-5 w-5 rounded border-border-dark bg-[#181111] text-primary focus:ring-offset-0 focus:ring-primary/50 transition-all checked:bg-primary checked:border-primary cursor-pointer">
                        </div>
                        <span class="text-sm text-[#b89d9f] group-hover:text-white transition-colors select-none">Ingat saya</span>
                    </label>
                    <!-- Optional: Forgot Password Link -->
                    <!-- <a href="#" class="text-sm font-medium text-primary hover:text-red-400 transition-colors">Lupa sandi?</a> -->
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-red-600 text-white font-bold py-4 rounded-xl shadow-[0_4px_14px_rgba(234,42,51,0.4)] hover:shadow-[0_6px_20px_rgba(234,42,51,0.6)] hover:-translate-y-0.5 active:translate-y-0 active:shadow-none transition-all text-sm uppercase tracking-wide flex items-center justify-center gap-3 group">
                    <span wire:loading.remove>Masuk ke Kasir</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform" wire:loading.remove>arrow_forward</span>
                </button>
            </form>

            <div class="pt-6 mt-6 border-t border-border-dark/50 text-center">
                <p class="text-[#b89d9f] text-sm">
                    Butuh bantuan? <a href="https://wa.me/082147205734" target="_blank" class="text-primary hover:underline font-medium">Click me!</a>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="absolute bottom-6 left-0 w-full text-center lg:text-left lg:pl-12">
            <p class="text-xs text-[#b89d9f]/40">© {{ date('Y') }} Nabila Store POS. All rights reserved.</p>
        </div>
    </div>
</div>
