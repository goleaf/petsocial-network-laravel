@extends('layouts.style-guide')

@section('content')
    <!-- Hero summary keeps stakeholders aligned on the goals of the style guide. -->
    <section class="rounded-2xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white p-8 shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-center gap-8">
            <div class="flex-1 space-y-4">
                <h1 class="text-3xl lg:text-4xl font-bold">PetSocial Network Experience System</h1>
                <p class="text-lg">
                    This interactive catalogue consolidates every core UI component and end-to-end page pattern required for
                    PetSocial Network. Use these canonical examples to maintain visual, behavioural, and accessibility
                    consistency across web and native touchpoints.
                </p>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur">Dark mode ready</span>
                    <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur">WCAG AA compliant</span>
                    <span class="px-3 py-1 rounded-full bg-white/20 backdrop-blur">Responsive verified</span>
                </div>
            </div>
            <div class="w-full lg:w-80">
                <div class="rounded-xl bg-white/15 p-6 shadow-lg">
                    <h2 class="text-xl font-semibold mb-4">Usage Checklist</h2>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-300"></span>
                            Reference components before building bespoke UI.
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-300"></span>
                            Keep motion and state logic aligned with Alpine snippets provided.
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-300"></span>
                            Update documentation alongside any functional change.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Core components anchor fundamental building blocks reused across the product. -->
    <section id="core-components" class="space-y-10">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <h2 class="text-2xl font-bold">Core Components</h2>
                <p class="text-slate-600 dark:text-slate-300">Reusable interface primitives covering interactive controls,
                    form elements, media, and feedback.</p>
            </div>
            <div class="flex gap-3 text-sm">
                <span class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-800">M = Must Have</span>
                <span class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-800">S = Should Have</span>
            </div>
        </div>
        <!-- Buttons collection demonstrates hierarchy, iconography, and floating actions. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Buttons, IconButtons, FAB <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Consistent sizing, color ramps, and focus outlines keep actions clear.</p>
                </div>
            </header>
            <div class="flex flex-wrap gap-4">
                <button class="px-4 py-2 rounded-full bg-indigo-600 text-white font-medium shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400">
                    Primary Action
                </button>
                <button class="px-4 py-2 rounded-full border border-indigo-300 text-indigo-600 font-medium hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-200">
                    Secondary Action
                </button>
                <button class="px-4 py-2 rounded-full border border-slate-300 text-slate-700 dark:text-slate-200 font-medium hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
                    Tertiary Action
                </button>
                <button class="h-10 w-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-300" aria-label="Icon Button">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
                <button class="px-5 py-3 rounded-xl bg-amber-500 text-white font-semibold shadow hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-300">
                    Destructive
                </button>
                <button class="px-4 py-2 rounded-full bg-rose-500 text-white font-medium shadow hover:bg-rose-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-300">
                    CTA with Icon
                </button>
                <button class="h-14 w-14 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-2xl hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-300" aria-label="Create">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
        </article>

        <!-- Inputs illustrate validation, helper text, and masking behaviours. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Inputs & Validation <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Masking and inline feedback reduce friction during data entry.</p>
                </div>
            </header>
            <div class="grid md:grid-cols-2 gap-6" x-data="{ email: '', error: '', masked: '', handleSubmit() { this.error = this.email.includes('@') ? '' : 'Please provide a valid email address.'; } }">
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Email Address</span>
                    <input x-model="email" type="email" placeholder="you@example.com" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-transparent px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />
                    <p x-text="error" class="text-sm text-rose-500" x-show="error" x-cloak></p>
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Phone (Masked)</span>
                    <input x-model="masked" x-on:input="masked = masked.replace(/[^0-9]/g, '').replace(/(\d{3})(\d{3})(\d{0,4})/, (_, a, b, c) => `${a}-${b}-${c}`.replace(/-$/, ''))" type="text" placeholder="555-123-4567" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-transparent px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />
                    <p class="text-sm text-slate-500">Automatically formats as the user types.</p>
                </label>
                <div class="space-y-2 md:col-span-2">
                    <textarea rows="3" placeholder="Share a quick update..." class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-transparent px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200"></textarea>
                    <p class="text-sm text-slate-500">Textarea scales up to 6 rows before introducing internal scrollbars.</p>
                </div>
                <div class="flex items-center gap-3 md:col-span-2">
                    <input type="password" value="Password123!" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-transparent px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />
                    <span class="px-3 py-1 rounded-full text-sm bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">Strong</span>
                </div>
                <button type="button" @click="handleSubmit()" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-300">Validate Email</button>
            </div>
        </article>
        <!-- Selection controls show dropdowns, multi-select, and combobox behaviours. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ selected: 'Husky', multi: ['Agility'], open: false, query: '', options: ['Obedience', 'Agility', 'Therapy', 'Rescue'], filtered() { return this.options.filter(option => option.toLowerCase().includes(this.query.toLowerCase())); }, toggleOption(option) { this.multi.includes(option) ? this.multi = this.multi.filter(item => item !== option) : this.multi.push(option); } }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Select, Multi-Select, Combobox <span class="text-sm text-slate-500">(S, M)</span></h3>
                    <p class="text-sm text-slate-500">Adaptive menus support keyboard navigation and filtered search.</p>
                </div>
            </header>
            <div class="grid md:grid-cols-3 gap-6">
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Pet Breed</span>
                    <select x-model="selected" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-transparent px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200">
                        <option>Husky</option>
                        <option>Corgi</option>
                        <option>Maine Coon</option>
                        <option>Parakeet</option>
                    </select>
                </label>
                <div class="space-y-2">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Interests</span>
                    <div class="rounded-lg border border-slate-300 dark:border-slate-700 p-3 space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="interest in options" :key="interest">
                                <button type="button" @click="toggleOption(interest)" class="px-3 py-1 rounded-full border border-indigo-300 text-sm" :class="multi.includes(interest) ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'text-slate-600 dark:text-slate-300'" x-text="interest"></button>
                            </template>
                        </div>
                        <p class="text-sm text-slate-500" x-text="`Selected: ${multi.join(', ')}`"></p>
                    </div>
                </div>
                <div class="space-y-2 relative">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Search Tags</span>
                    <div class="rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7 7 0 1010.3 17l4.35 4.35z" />
                        </svg>
                        <input type="text" x-model="query" @focus="open = true" placeholder="Type to filter" class="flex-1 bg-transparent focus:outline-none" />
                    </div>
                    <div x-show="open" @click.away="open = false" class="absolute mt-2 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg z-10" x-cloak>
                        <template x-if="filtered().length">
                            <ul class="max-h-40 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="option in filtered()" :key="option">
                                    <li>
                                        <button type="button" @click="query = option; open = false" class="w-full text-left px-4 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-500/10" x-text="option"></button>
                                    </li>
                                </template>
                            </ul>
                        </template>
                        <p x-show="!filtered().length" class="px-4 py-2 text-sm text-slate-500">No tags found.</p>
                    </div>
                </div>
            </div>
        </article>

        <!-- Chips and tags highlight filter tokens and interactive labels. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Chips & Tags <span class="text-sm text-slate-500">(S, S)</span></h3>
                    <p class="text-sm text-slate-500">Compact tokens support filters, selections, and inline status.</p>
                </div>
            </header>
            <div class="flex flex-wrap gap-3">
                <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium">Training</span>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-sm font-medium">Adoption</span>
                <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-sm font-medium flex items-center gap-2">
                    Needs Vet
                    <button aria-label="Remove" class="h-5 w-5 rounded-full bg-rose-500 text-white flex items-center justify-center">×</button>
                </span>
                <button class="px-3 py-1 rounded-full border border-slate-300 text-sm text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
                    + Add Tag
                </button>
            </div>
        </article>
        <!-- Date and time inputs align with scheduling and timeline requirements. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Date & Time Pickers <span class="text-sm text-slate-500">(S, M)</span></h3>
                    <p class="text-sm text-slate-500">Native inputs complemented by quick presets for rapid planning.</p>
                </div>
            </header>
            <div class="grid md:grid-cols-3 gap-6" x-data="{ rangeStart: '', rangeEnd: '', preset(dayOffset) { const base = new Date(); base.setDate(base.getDate() + dayOffset); return base.toISOString().split('T')[0]; } }">
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Appointment Date</span>
                    <input type="date" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />
                </label>
                <label class="space-y-2">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Reminder Time</span>
                    <input type="time" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" />
                </label>
                <div class="space-y-3">
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Date Range</span>
                    <div class="flex gap-3">
                        <input x-model="rangeStart" type="date" class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" placeholder="Start" />
                        <input x-model="rangeEnd" type="date" class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200" placeholder="End" />
                    </div>
                    <div class="flex gap-2 text-xs">
                        <button type="button" @click="rangeStart = preset(0); rangeEnd = preset(7);" class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-800">Next 7 days</button>
                        <button type="button" @click="rangeStart = preset(-7); rangeEnd = preset(0);" class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-800">Previous Week</button>
                    </div>
                </div>
            </div>
        </article>

        <!-- File uploader covers drag-drop, progress, and retry flows. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ isDropping: false, progress: 45, status: 'Uploading paw-print.png…', hasError: false, retry() { this.hasError = false; this.progress = 10; this.status = 'Retrying upload…'; setTimeout(() => { this.progress = 100; this.status = 'Upload complete!'; }, 1500); } }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">File Uploader <span class="text-sm text-slate-500">(S, M)</span></h3>
                    <p class="text-sm text-slate-500">Drag-and-drop zone with progress, cancellation, and retry affordances.</p>
                </div>
            </header>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="border-2 border-dashed rounded-xl p-6 text-center transition" :class="isDropping ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 dark:border-slate-700'" @dragenter.prevent="isDropping = true" @dragleave.prevent="isDropping = false" @drop.prevent="isDropping = false">
                    <p class="font-medium">Drag & drop files here</p>
                    <p class="text-sm text-slate-500 mt-2">JPEG, PNG, GIF up to 50MB</p>
                    <button class="mt-4 px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-500">Select Files</button>
                </div>
                <div class="space-y-4">
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-4 flex gap-4 items-start">
                        <div class="h-12 w-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-semibold">PNG</div>
                        <div class="flex-1 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span x-text="status"></span>
                                <span x-text="`${progress}%`"></span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div class="h-full bg-indigo-500" :style="`width: ${progress}%`"></div>
                            </div>
                            <div class="flex gap-2 text-sm">
                                <button class="px-3 py-1 rounded-full bg-rose-100 text-rose-600">Cancel</button>
                                <button @click="retry()" x-show="hasError" x-cloak class="px-3 py-1 rounded-full bg-amber-100 text-amber-600">Retry</button>
                                <button class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-800">Edit & Crop</button>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-dashed border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-500">
                        Optional image cropper launches once upload completes.
                    </div>
                </div>
            </div>
        </article>
        <!-- Avatar treatments communicate presence and status clarity. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Avatar with Status Ring <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Layered borders and tooltips convey presence and availability.</p>
                </div>
            </header>
            <div class="flex flex-wrap gap-6 items-center">
                <div class="relative">
                    <div class="h-16 w-16 rounded-full border-4 border-emerald-400 p-1 bg-white dark:bg-slate-900">
                        <img src="https://placekitten.com/200/200" alt="Pet avatar" class="h-full w-full rounded-full object-cover">
                    </div>
                    <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900"></span>
                </div>
                <div class="relative">
                    <div class="h-16 w-16 rounded-full border-4 border-amber-400 p-1 bg-white dark:bg-slate-900">
                        <img src="https://placebear.com/200/200" alt="Owner avatar" class="h-full w-full rounded-full object-cover">
                    </div>
                    <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-amber-500 border-2 border-white dark:border-slate-900"></span>
                </div>
                <div class="relative">
                    <div class="h-16 w-16 rounded-full border-4 border-slate-400 p-1 bg-white dark:bg-slate-900">
                        <img src="https://placekitten.com/201/201" alt="Offline avatar" class="h-full w-full rounded-full object-cover opacity-80">
                    </div>
                    <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-slate-500 border-2 border-white dark:border-slate-900"></span>
                </div>
            </div>
        </article>

        <!-- Cards and lists supply structured content presentation. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Card & List Patterns <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Cards highlight primary actions; condensed lists maximise density.</p>
                </div>
            </header>
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                    <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?auto=format&fit=crop&w=800&q=80" alt="Dog running" class="h-48 w-full object-cover">
                    <div class="p-5 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full overflow-hidden">
                                <img src="https://placekitten.com/100/100" alt="Avatar" class="h-full w-full object-cover">
                            </div>
                            <div>
                                <p class="font-semibold">Nova the Husky</p>
                                <p class="text-xs text-slate-500">2 hours ago · Anchorage, AK</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Blasted through the first snow of the season! Tips for keeping paws protected?</p>
                        <div class="flex gap-3 text-sm">
                            <button class="flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">❤️ <span>148</span></button>
                            <button class="flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">💬 <span>18</span></button>
                            <button class="flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">🔁 <span>4</span></button>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-200 dark:divide-slate-800">
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                            <div>
                                <p class="font-medium">Scheduled Vet Visit</p>
                                <p class="text-xs text-slate-500">Nov 24 · Anchorage Pet Clinic</p>
                            </div>
                        </div>
                        <button class="text-sm text-indigo-600">Details</button>
                    </div>
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                            <div>
                                <p class="font-medium">Bath Time Reminder</p>
                                <p class="text-xs text-slate-500">Tomorrow · 6:00 PM</p>
                            </div>
                        </div>
                        <button class="text-sm text-indigo-600">Snooze</button>
                    </div>
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                            <div>
                                <p class="font-medium">Renew Vaccinations</p>
                                <p class="text-xs text-slate-500">Dec 03 · Upload records</p>
                            </div>
                        </div>
                        <button class="text-sm text-indigo-600">Update</button>
                    </div>
                </div>
            </div>
        </article>

        <!-- Tabs and accordions offer content organization patterns. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ activeTab: 'posts', accordion: { profile: true, security: false, notifications: false } }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Tabs & Accordions <span class="text-sm text-slate-500">(S, S)</span></h3>
                    <p class="text-sm text-slate-500">Adaptive structures switch between horizontal tabs and vertical accordions responsively.</p>
                </div>
            </header>
            <div class="space-y-6">
                <nav class="flex flex-wrap gap-2">
                    <button @click="activeTab = 'posts'" :class="activeTab === 'posts' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-full text-sm font-medium">Posts</button>
                    <button @click="activeTab = 'blogs'" :class="activeTab === 'blogs' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-full text-sm font-medium">Blogs</button>
                    <button @click="activeTab = 'pets'" :class="activeTab === 'pets' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-4 py-2 rounded-full text-sm font-medium">Pets</button>
                </nav>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                    <template x-if="activeTab === 'posts'">
                        <div class="space-y-3">
                            <h4 class="font-semibold">Recent Posts</h4>
                            <p class="text-sm text-slate-500">Surface short-form updates with media previews and quick reactions.</p>
                        </div>
                    </template>
                    <template x-if="activeTab === 'blogs'">
                        <div class="space-y-3">
                            <h4 class="font-semibold">Featured Blogs</h4>
                            <p class="text-sm text-slate-500">Highlight long-form storytelling with estimated reading time.</p>
                        </div>
                    </template>
                    <template x-if="activeTab === 'pets'">
                        <div class="space-y-3">
                            <h4 class="font-semibold">Pet Gallery</h4>
                            <p class="text-sm text-slate-500">Showcase pet bios with quick links to follow and message owners.</p>
                        </div>
                    </template>
                </div>
                <div class="space-y-4">
                    <template x-for="(open, section) in accordion" :key="section">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <button type="button" @click="accordion[section] = !accordion[section]" class="w-full flex items-center justify-between px-5 py-3 bg-slate-100 dark:bg-slate-800">
                                <span class="font-medium text-sm uppercase" x-text="section"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition" :class="accordion[section] ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="accordion[section]" x-transition.opacity class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300" x-cloak>
                                Content tailored for the <span class="font-semibold" x-text="section"></span> preferences panel, including toggles, descriptive help text, and CTA links.
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </article>
        <!-- Modal and drawer patterns centralise blocking and non-blocking overlays. -->
        <article id="interaction-library" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ showModal: false, showDrawer: false }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Modal, Drawer, Dropdown <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Overlay elements respect focus traps, escape handling, and accessibility labelling.</p>
                </div>
            </header>
            <div class="flex flex-wrap gap-4">
                <button @click="showModal = true" class="px-4 py-2 rounded-lg bg-indigo-600 text-white">Open Modal</button>
                <button @click="showDrawer = true" class="px-4 py-2 rounded-lg bg-slate-900 text-white dark:bg-slate-700">Open Drawer</button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="px-4 py-2 rounded-lg border border-slate-300">Dropdown Menu</button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute mt-2 w-48 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg">
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-500/10">Edit</a>
                        <a href="#" class="block px-4 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-indigo-500/10">Share</a>
                        <a href="#" class="block px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">Report</a>
                    </div>
                </div>
            </div>
            <div x-show="showModal" x-cloak class="fixed inset-0 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
                <div class="relative max-w-lg w-full rounded-2xl bg-white dark:bg-slate-900 p-6 space-y-4">
                    <button class="absolute top-4 right-4 text-slate-500" @click="showModal = false">×</button>
                    <h4 class="text-xl font-semibold">Create New Event</h4>
                    <p class="text-sm text-slate-500">Schedule meetups, play dates, or training sessions. All attendees receive notifications.</p>
                    <input type="text" placeholder="Event title" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2" />
                    <div class="flex justify-end gap-3">
                        <button @click="showModal = false" class="px-4 py-2 rounded-lg border border-slate-300">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white">Create Event</button>
                    </div>
                </div>
            </div>
            <div x-show="showDrawer" x-cloak class="fixed inset-0 flex justify-end bg-slate-900/40">
                <div class="w-full sm:w-96 h-full bg-white dark:bg-slate-900 shadow-xl p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h4 class="text-lg font-semibold">Mobile Sheet</h4>
                        <button @click="showDrawer = false" class="text-slate-500">Close</button>
                    </div>
                    <p class="text-sm text-slate-500">Use drawers for non-blocking context panels like filters or quick compose.</p>
                    <div class="space-y-3">
                        <label class="space-y-2">
                            <span class="text-sm font-medium">Filter by content type</span>
                            <select class="w-full rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2">
                                <option>All</option>
                                <option>Photos</option>
                                <option>Blogs</option>
                                <option>Events</option>
                            </select>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium">Only show followed pets</span>
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 rounded" checked>
                        </label>
                    </div>
                    <button class="w-full py-2 rounded-lg bg-indigo-600 text-white">Apply Filters</button>
                </div>
            </div>
        </article>
        <!-- Pagination and infinite scroll guidelines for feed navigation. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ loading: false, items: 6, loadMore() { if (this.loading) return; this.loading = true; setTimeout(() => { this.items += 3; this.loading = false; }, 1000); } }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Pagination & Infinite Scroll <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Hybrid approach combines numbered pagination with seamless load-more interactions.</p>
                </div>
            </header>
            <div class="grid md:grid-cols-3 gap-4">
                <template x-for="index in items" :key="index">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-2">
                        <div class="h-24 rounded-lg bg-slate-100 dark:bg-slate-800"></div>
                        <p class="font-medium">Post Card #<span x-text="index"></span></p>
                        <p class="text-xs text-slate-500">Scrolling appends more cards while preserving position.</p>
                    </div>
                </template>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button class="px-3 py-1 rounded-full border border-slate-300">Previous</button>
                    <div class="flex gap-1 text-sm">
                        <button class="h-8 w-8 rounded-full bg-indigo-600 text-white">1</button>
                        <button class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-800">2</button>
                        <button class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-800">3</button>
                    </div>
                    <button class="px-3 py-1 rounded-full border border-slate-300">Next</button>
                </div>
                <button @click="loadMore()" class="px-4 py-2 rounded-lg bg-slate-900 text-white dark:bg-slate-700 flex items-center gap-2" :disabled="loading">
                    <svg x-show="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v2m0 12v2m8-8h-2M6 12H4m13.364 6.364l-1.414-1.414M8.05 8.05 6.636 6.636m0 10.728 1.414-1.414M17.95 8.05l1.414-1.414" />
                    </svg>
                    <span x-text="loading ? 'Loading…' : 'Load more posts'"></span>
                </button>
            </div>
        </article>

        <!-- Breadcrumbs ensure navigation clarity for nested destinations. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Breadcrumbs <span class="text-sm text-slate-500">(S, S)</span></h3>
                    <p class="text-sm text-slate-500">Provide hierarchical context and quick hops back to parent views.</p>
                </div>
            </header>
            <nav class="flex items-center gap-2 text-sm">
                <a href="#" class="text-indigo-600">Home</a>
                <span>/</span>
                <a href="#" class="text-indigo-600">Pets</a>
                <span>/</span>
                <a href="#" class="text-indigo-600">Nova the Husky</a>
                <span>/</span>
                <span class="text-slate-500">Medical Records</span>
            </nav>
        </article>

        <!-- Search bar with autocomplete merges saved searches and trending tags. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ query: '', suggestions: ['Husky hiking tips', 'Best grain-free food', 'Puppy training classes', 'Pet-friendly cafes'], history: ['Last vet visit', 'Adoption paperwork'], focused: false, get filtered() { return this.query ? this.suggestions.filter(item => item.toLowerCase().includes(this.query.toLowerCase())) : this.suggestions; } }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Search with Autocomplete <span class="text-sm text-slate-500">(M, M)</span></h3>
                    <p class="text-sm text-slate-500">Combines global search with smart suggestions and recent history.</p>
                </div>
            </header>
            <div class="relative max-w-2xl">
                <div class="flex items-center gap-3 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7 7 0 1010.3 17l4.35 4.35z" />
                    </svg>
                    <input type="text" x-model="query" @focus="focused = true" placeholder="Search across posts, pets, blogs…" class="flex-1 bg-transparent focus:outline-none" />
                    <button class="text-sm px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">⌘K</button>
                </div>
                <div x-show="focused" @click.away="focused = false" x-cloak class="absolute mt-3 w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-2xl overflow-hidden">
                    <div class="px-4 py-3 text-xs font-semibold uppercase text-slate-500">Suggestions</div>
                    <template x-if="filtered.length">
                        <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                            <template x-for="item in filtered" :key="item">
                                <li>
                                    <button class="w-full text-left px-4 py-2 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 text-sm" x-text="item"></button>
                                </li>
                            </template>
                        </ul>
                    </template>
                    <div class="px-4 py-3 text-xs font-semibold uppercase text-slate-500">Recent Searches</div>
                    <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                        <template x-for="item in history" :key="item">
                            <li class="px-4 py-2 text-sm text-slate-500" x-text="item"></li>
                        </template>
                    </ul>
                </div>
            </div>
        </article>
        <!-- Toasts and snackbars deliver lightweight feedback cues. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ toasts: [], push(message, tone = 'success') { const id = Date.now(); this.toasts.push({ id, message, tone }); setTimeout(() => this.toasts = this.toasts.filter(toast => toast.id !== id), 4000); } }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Toast & Snackbar <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Stackable notifications animate into view without blocking user focus.</p>
                </div>
            </header>
            <div class="flex flex-wrap gap-3">
                <button @click="push('Post published successfully!')" class="px-4 py-2 rounded-lg bg-emerald-500 text-white">Trigger Success</button>
                <button @click="push('We could not schedule the post.', 'error')" class="px-4 py-2 rounded-lg bg-rose-500 text-white">Trigger Error</button>
                <button @click="push('Draft saved and synced offline.', 'info')" class="px-4 py-2 rounded-lg bg-indigo-500 text-white">Trigger Info</button>
            </div>
            <div class="fixed bottom-6 right-6 space-y-3" aria-live="assertive">
                <template x-for="toast in toasts" :key="toast.id">
                    <div class="w-72 rounded-xl px-4 py-3 shadow-lg text-white" :class="{ 'bg-emerald-500': toast.tone === 'success', 'bg-rose-500': toast.tone === 'error', 'bg-indigo-500': toast.tone === 'info' }">
                        <p class="font-medium" x-text="toast.message"></p>
                    </div>
                </template>
            </div>
        </article>

        <!-- Tooltip and hover card patterns communicate supplemental context. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ hover: false }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Tooltip & Hover Card <span class="text-sm text-slate-500">(S, S)</span></h3>
                    <p class="text-sm text-slate-500">Micro-interactions expose definitions, bios, and quick links.</p>
                </div>
            </header>
            <div class="flex gap-10">
                <button class="relative px-4 py-2 rounded-lg bg-slate-900 text-white dark:bg-slate-700" @mouseenter="hover = true" @mouseleave="hover = false">
                    Hover for tooltip
                    <span x-show="hover" x-cloak class="absolute -top-12 left-1/2 -translate-x-1/2 px-3 py-1.5 rounded bg-slate-900 text-white text-xs">Helpful explanation text</span>
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button class="px-4 py-2 rounded-lg border border-slate-300" @mouseenter="open = true" @mouseleave="open = false">Hover card</button>
                    <div x-show="open" x-cloak class="absolute top-12 w-64 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-xl">
                        <div class="flex items-center gap-3">
                            <img src="https://placekitten.com/120/120" alt="Hover avatar" class="h-12 w-12 rounded-full object-cover">
                            <div>
                                <p class="font-semibold">Nova the Husky</p>
                                <p class="text-xs text-slate-500">Snow trail enthusiast</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">Follow to see Nova's latest mountain adventures and training tips.</p>
                        <div class="mt-4 flex gap-2">
                            <button class="flex-1 px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Follow</button>
                            <button class="flex-1 px-3 py-1 rounded-full border border-slate-300 text-sm">Message</button>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Progress indicators and skeleton loaders address waiting states. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Progress & Skeleton Loaders <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Visual placeholders preserve layout integrity while data loads.</p>
                </div>
            </header>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Linear Progress</p>
                    <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-indigo-500" style="width: 65%"></div>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Circular Progress</p>
                    <div class="h-20 w-20 rounded-full border-4 border-slate-200 dark:border-slate-700 border-t-indigo-500 animate-spin"></div>
                </div>
                <div class="space-y-4">
                    <div class="animate-pulse space-y-3">
                        <div class="h-4 w-3/4 rounded bg-slate-200 dark:bg-slate-700"></div>
                        <div class="h-4 w-full rounded bg-slate-200 dark:bg-slate-700"></div>
                        <div class="h-4 w-5/6 rounded bg-slate-200 dark:bg-slate-700"></div>
                    </div>
                    <div class="animate-pulse space-y-3">
                        <div class="h-52 w-full rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
                        <div class="h-4 w-1/2 rounded bg-slate-200 dark:bg-slate-700"></div>
                    </div>
                </div>
            </div>
        </article>
        <!-- Stepper outlines onboarding progress with action shortcuts. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ step: 2 }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Stepper (Onboarding) <span class="text-sm text-slate-500">(S, S)</span></h3>
                    <p class="text-sm text-slate-500">Guides new members from profile basics to friend discovery.</p>
                </div>
            </header>
            <div class="space-y-6">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-4">
                    <div class="flex items-center gap-4">
                        <template x-for="index in 3" :key="index">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center" :class="step >= index ? 'bg-indigo-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'" x-text="index"></div>
                                <template x-if="index < 3">
                                    <div class="hidden md:block h-0.5 w-20" :class="step > index ? 'bg-indigo-500' : 'bg-slate-200 dark:bg-slate-700'"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="flex gap-2 text-sm">
                        <button @click="step = Math.max(1, step - 1)" class="px-3 py-1 rounded-full border border-slate-300">Back</button>
                        <button @click="step = Math.min(3, step + 1)" class="px-3 py-1 rounded-full bg-indigo-600 text-white">Continue</button>
                        <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Skip for now</button>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-5">
                    <template x-if="step === 1">
                        <div>
                            <h4 class="font-semibold">Step 1 · Profile Setup</h4>
                            <p class="text-sm text-slate-500">Upload an avatar, add a short bio, and set your location.</p>
                        </div>
                    </template>
                    <template x-if="step === 2">
                        <div>
                            <h4 class="font-semibold">Step 2 · First Pet</h4>
                            <p class="text-sm text-slate-500">Share your pet's story, favourite activities, and medical notes.</p>
                        </div>
                    </template>
                    <template x-if="step === 3">
                        <div>
                            <h4 class="font-semibold">Step 3 · Follow Suggestions</h4>
                            <p class="text-sm text-slate-500">Follow recommended pets and owners to personalise your feed.</p>
                        </div>
                    </template>
                </div>
            </div>
        </article>

        <!-- Media presentation combines lightbox and carousel flows. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ open: false, active: 0, images: [
            'https://images.unsplash.com/photo-1507149833265-60c372daea22?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80'
        ] }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Lightbox & Carousel <span class="text-sm text-slate-500">(S, M)</span></h3>
                    <p class="text-sm text-slate-500">Supports galleries, media viewers, and immersive storytelling.</p>
                </div>
            </header>
            <div class="grid md:grid-cols-3 gap-4">
                <template x-for="(image, index) in images" :key="image">
                    <button @click="active = index; open = true" class="relative h-32 rounded-xl overflow-hidden group">
                        <img :src="image" alt="Gallery item" class="h-full w-full object-cover">
                        <span class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white">View</span>
                    </button>
                </template>
            </div>
            <div x-show="open" x-cloak class="fixed inset-0 bg-slate-900/90 flex flex-col items-center justify-center gap-6 p-6">
                <button class="self-end text-white text-2xl" @click="open = false">×</button>
                <div class="relative w-full max-w-3xl">
                    <img :src="images[active]" alt="Expanded" class="w-full rounded-2xl object-cover shadow-2xl">
                    <button class="absolute top-1/2 -left-4 -translate-y-1/2 h-10 w-10 rounded-full bg-white/80 text-slate-900" @click="active = (active - 1 + images.length) % images.length">‹</button>
                    <button class="absolute top-1/2 -right-4 -translate-y-1/2 h-10 w-10 rounded-full bg-white/80 text-slate-900" @click="active = (active + 1) % images.length">›</button>
                </div>
                <div class="flex gap-3">
                    <template x-for="(image, index) in images" :key="image + index">
                        <button class="h-3 w-3 rounded-full" :class="active === index ? 'bg-white' : 'bg-white/40'" @click="active = index"></button>
                    </template>
                </div>
            </div>
        </article>

        <!-- Markdown renderer preview ensures formatted content appears predictably. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Markdown Renderer <span class="text-sm text-slate-500">(S, M)</span></h3>
                    <p class="text-sm text-slate-500">Supports headings, lists, quotes, and code blocks with consistent styling.</p>
                </div>
            </header>
            <article class="prose dark:prose-invert max-w-none">
                <h4>Trail Safety Checklist</h4>
                <p>Before hitting the trail with your pet, remember to pack these essentials:</p>
                <ul>
                    <li><strong>Hydration</strong> – collapsible water bowl and extra water.</li>
                    <li><strong>Paw care</strong> – balm or booties to protect against rough terrain.</li>
                    <li><strong>Emergency kit</strong> – bandages, tweezers, and vet contact info.</li>
                </ul>
                <blockquote>“Always check the forecast and trail reports before you leave.”</blockquote>
                <pre><code class="language-bash"># Share hike details with friends
echo "Heading out on the Skyline Trail" | post_to_petsocial</code></pre>
            </article>
        </article>

        <!-- Emoji and GIF picker bring expressive communication to life. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ emojis: ['🐾', '😺', '🐶', '🦴', '🦜', '🐢'], gifs: ['Excited dog', 'Dancing cat', 'Flying parrot'], mode: 'emoji', recent: [] }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Emoji & GIF Picker <span class="text-sm text-slate-500">(S, M)</span></h3>
                    <p class="text-sm text-slate-500">Searchable palette with recents and category tabs.</p>
                </div>
            </header>
            <div class="space-y-4">
                <div class="flex gap-2 text-sm">
                    <button @click="mode = 'emoji'" :class="mode === 'emoji' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800'" class="px-3 py-1 rounded-full">Emoji</button>
                    <button @click="mode = 'gif'" :class="mode === 'gif' ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800'" class="px-3 py-1 rounded-full">GIFs</button>
                </div>
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                    <template x-if="mode === 'emoji'">
                        <div class="grid grid-cols-6 gap-3 text-2xl">
                            <template x-for="emoji in emojis" :key="emoji">
                                <button @click="recent.unshift(emoji); recent = recent.slice(0, 6);" class="h-12 w-12 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" x-text="emoji"></button>
                            </template>
                        </div>
                    </template>
                    <template x-if="mode === 'gif'">
                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <template x-for="gif in gifs" :key="gif">
                                <button class="h-24 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center" x-text="gif"></button>
                            </template>
                        </div>
                    </template>
                </div>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase text-slate-500">Recent</p>
                    <div class="flex gap-2">
                        <template x-for="emoji in recent" :key="emoji">
                            <span class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xl" x-text="emoji"></span>
                        </template>
                        <p x-show="!recent.length" class="text-sm text-slate-500">Select an emoji to populate recents.</p>
                    </div>
                </div>
            </div>
        </article>

        <!-- Reporting flows include modals and confirmation sequences. -->
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm space-y-6" x-data="{ openReport: false, confirmDelete: false }">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Report & Confirmation Dialogs <span class="text-sm text-slate-500">(M, S)</span></h3>
                    <p class="text-sm text-slate-500">Sensitive flows reinforce intent with multi-step acknowledgement.</p>
                </div>
            </header>
            <div class="flex gap-3">
                <button @click="openReport = true" class="px-4 py-2 rounded-lg bg-rose-500 text-white">Report content</button>
                <button @click="confirmDelete = true" class="px-4 py-2 rounded-lg bg-amber-500 text-white">Delete post</button>
            </div>
            <div x-show="openReport" x-cloak class="fixed inset-0 bg-slate-900/70 flex items-center justify-center p-4">
                <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 p-6 space-y-4">
                    <h4 class="text-lg font-semibold">Report this post</h4>
                    <p class="text-sm text-slate-500">Select a category and provide optional details. Reports trigger moderator workflows instantly.</p>
                    <select class="w-full rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2">
                        <option>Spam</option>
                        <option>Harassment</option>
                        <option>Misinformation</option>
                        <option>Animal welfare concern</option>
                    </select>
                    <textarea rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-2" placeholder="Tell us what happened"></textarea>
                    <div class="flex justify-end gap-3">
                        <button @click="openReport = false" class="px-4 py-2 rounded-lg border border-slate-300">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-rose-500 text-white">Submit report</button>
                    </div>
                </div>
            </div>
            <div x-show="confirmDelete" x-cloak class="fixed inset-0 bg-slate-900/60 flex items-center justify-center p-4">
                <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 p-6 space-y-4">
                    <h4 class="text-lg font-semibold text-rose-600">Delete this post?</h4>
                    <p class="text-sm text-slate-500">This action cannot be undone. Comments and reactions will also be removed.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="confirmDelete = false" class="px-4 py-2 rounded-lg border border-slate-300">Cancel</button>
                        <button class="px-4 py-2 rounded-lg bg-rose-500 text-white">Yes, delete</button>
                    </div>
                </div>
            </div>
        </article>
    </section>
    <!-- Page patterns translate components into narrative flows across primary screens. -->
    <section id="page-patterns" class="space-y-10">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <h2 class="text-2xl font-bold">Page Patterns</h2>
                <p class="text-slate-600 dark:text-slate-300">Blueprint layouts for every mission-critical page, optimised for desktop and mobile.</p>
            </div>
            <div class="flex gap-3 text-sm">
                <span class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-800">Primary funnel journeys</span>
                <span class="px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-800">Responsive & accessible</span>
            </div>
        </div>

        <!-- Landing page hero and testimonials illustrate value prop storytelling. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-xl">
            <div class="grid lg:grid-cols-2">
                <div class="p-10 space-y-6 bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                    <h3 class="text-3xl font-bold">Landing · Hero + Value Props + CTA <span class="text-base">(M, S)</span></h3>
                    <p class="text-lg">Celebrate the human–pet bond with a vibrant hero, key differentiators, and a decisive call-to-action.</p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center text-xl">🐾</span>
                            <div>
                                <p class="font-semibold">Unified Pet & Owner Profiles</p>
                                <p class="text-sm">Give every pet a spotlight with timeline, gallery, and milestones.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center text-xl">⚡️</span>
                            <div>
                                <p class="font-semibold">Real-time Moments</p>
                                <p class="text-sm">Capture reactions instantly with live updates and notifications.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center text-xl">🛡️</span>
                            <div>
                                <p class="font-semibold">Privacy by Design</p>
                                <p class="text-sm">Granular controls protect sensitive information without friction.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button class="px-5 py-3 rounded-full bg-white text-indigo-600 font-semibold">Join PetSocial</button>
                        <button class="px-5 py-3 rounded-full bg-white/20 text-white border border-white/40">Explore features</button>
                    </div>
                </div>
                <div class="p-10 space-y-6">
                    <h4 class="text-xl font-semibold">Social Proof & Testimonials <span class="text-sm text-slate-500">(C, S)</span></h4>
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
                            <p class="text-sm italic">“PetSocial helped us find Nova's littermates in days. The onboarding was effortless!”</p>
                            <div class="mt-3 flex items-center gap-3">
                                <img src="https://placekitten.com/88/88" class="h-10 w-10 rounded-full" alt="Testimonial avatar">
                                <div>
                                    <p class="font-semibold">Alex & Nova</p>
                                    <p class="text-xs text-slate-500">Anchorage, Alaska</p>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
                            <p class="text-sm italic">“The unified search and friend suggestions make it feel like a true community.”</p>
                            <p class="text-xs text-slate-500 mt-2">— Priya, Cat foster parent</p>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Authentication flows emphasise clarity, inline errors, and alternative sign-in. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Auth · Clean Forms & Magic Link <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Authentication screens highlight inline validation, password strength, and optional passwordless entry.</p>
            </header>
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <label class="space-y-2">
                        <span class="text-sm font-semibold">Email</span>
                        <input type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="you@example.com">
                        <span class="text-xs text-rose-500">Please enter a valid email.</span>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-semibold">Password</span>
                        <div class="rounded-lg border border-slate-300 px-3 py-2 flex items-center justify-between">
                            <input type="password" class="flex-1 bg-transparent" value="Secret123!">
                            <span class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full">Strong</span>
                        </div>
                    </label>
                    <button class="w-full py-3 rounded-lg bg-indigo-600 text-white font-semibold">Sign In</button>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span>or</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>
                    <button class="w-full py-3 rounded-lg border border-slate-300">Send me a magic link</button>
                </div>
                <div class="rounded-2xl border border-dashed border-slate-300 p-6 space-y-3">
                    <h4 class="font-semibold">Developer Notes</h4>
                    <ul class="list-disc list-inside text-sm text-slate-600 dark:text-slate-300 space-y-2">
                        <li>Password strength meter surfaces after 3 characters and updates on keypress.</li>
                        <li>Magic link flow reuses existing email templates with branding override.</li>
                        <li>Inline errors animate into place and use polite ARIA live regions.</li>
                    </ul>
                </div>
            </div>
        </article>

        <!-- Onboarding journey ensures progressive profiling with skip logic. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8" x-data="{ completed: ['Profile'], current: 'First Pet' }">
            <header>
                <h3 class="text-2xl font-bold">Onboarding · Profile → Pet → Suggestions <span class="text-base">(M, M)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Progress indicator and skip-with-reminder flows keep momentum while respecting user intent.</p>
            </header>
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="lg:w-1/3 space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-sm text-slate-500">Progress</p>
                        <div class="mt-3 flex items-center gap-3">
                            <div class="flex-1 h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-indigo-500" style="width: 45%"></div>
                            </div>
                            <span class="text-sm font-semibold">45%</span>
                        </div>
                    </div>
                    <button class="w-full py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-sm">Skip step · remind me later</button>
                </div>
                <div class="flex-1 space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                        <h4 class="font-semibold">Step 2 · First Pet</h4>
                        <p class="text-sm text-slate-500">Add your pet's essentials to unlock personalised suggestions.</p>
                        <div class="mt-4 grid md:grid-cols-2 gap-4">
                            <input type="text" placeholder="Pet name" class="rounded-lg border border-slate-300 px-3 py-2">
                            <select class="rounded-lg border border-slate-300 px-3 py-2">
                                <option>Dog</option>
                                <option>Cat</option>
                                <option>Bird</option>
                            </select>
                            <input type="date" class="rounded-lg border border-slate-300 px-3 py-2">
                            <input type="text" placeholder="Favourite activity" class="rounded-lg border border-slate-300 px-3 py-2">
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                        <h4 class="font-semibold">Step 3 Preview · Follow Suggestions</h4>
                        <p class="text-sm text-slate-500">Surface recommended pets and owners with follow CTAs and context.</p>
                        <div class="mt-4 grid md:grid-cols-3 gap-3">
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 text-center">
                                <img src="https://placekitten.com/120/121" class="mx-auto h-16 w-16 rounded-full" alt="Follow suggestion">
                                <p class="mt-2 font-medium">Luna the Shepherd</p>
                                <button class="mt-2 px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Follow</button>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 text-center">
                                <img src="https://placekitten.com/121/121" class="mx-auto h-16 w-16 rounded-full" alt="Follow suggestion">
                                <p class="mt-2 font-medium">Mochi the Cat</p>
                                <button class="mt-2 px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Follow</button>
                            </div>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3 text-center">
                                <img src="https://placekitten.com/122/122" class="mx-auto h-16 w-16 rounded-full" alt="Follow suggestion">
                                <p class="mt-2 font-medium">Kiko the Parrot</p>
                                <button class="mt-2 px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Follow</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <!-- Feed layout balances composer, filters, and post presentation. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8" x-data="{ filter: 'All' }">
            <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold">Feed · Composer, Filters, Sticky CTA <span class="text-base">(M, M)</span></h3>
                    <p class="text-slate-600 dark:text-slate-300">The feed keeps the composer accessible while filters and cards deliver high-signal content.</p>
                </div>
                <button class="px-4 py-2 rounded-full bg-indigo-600 text-white lg:hidden">Create Post</button>
            </header>
            <div class="grid lg:grid-cols-[320px,1fr] gap-6">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <img src="https://placekitten.com/90/90" class="h-12 w-12 rounded-full" alt="Composer avatar">
                            <select class="rounded-lg border border-slate-300 px-3 py-2 flex-1">
                                <option>Posting as Nova</option>
                                <option>Posting as Alex</option>
                            </select>
                        </div>
                        <textarea rows="3" class="w-full rounded-xl border border-slate-300 px-3 py-2" placeholder="Share a moment…"></textarea>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex gap-2">
                                <button class="px-3 py-1 rounded-full border border-slate-300">Photo</button>
                                <button class="px-3 py-1 rounded-full border border-slate-300">Blog</button>
                                <button class="px-3 py-1 rounded-full border border-slate-300">Event</button>
                            </div>
                            <button class="px-4 py-2 rounded-full bg-indigo-600 text-white">Post</button>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                        <h4 class="font-semibold mb-3">Filters (All/Photos/Blogs) <span class="text-sm text-slate-500">(S, M)</span></h4>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="option in ['All', 'Photos', 'Blogs']" :key="option">
                                <button @click="filter = option" class="px-3 py-1 rounded-full" :class="filter === option ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800'" x-text="option"></button>
                            </template>
                        </div>
                    </div>
                    <div class="hidden lg:block sticky top-6">
                        <button class="w-full py-3 rounded-full bg-indigo-600 text-white shadow-lg">Create Post</button>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
                        <div class="flex items-center gap-3">
                            <img src="https://placekitten.com/95/95" class="h-12 w-12 rounded-full" alt="Post avatar">
                            <div>
                                <p class="font-semibold">Nova the Husky</p>
                                <p class="text-xs text-slate-500">1 hour ago · Friends only</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Captured the first snow sprint! ❄️</p>
                        <img src="https://images.unsplash.com/photo-1444212477490-ca407925329e?auto=format&fit=crop&w=1200&q=80" class="rounded-xl" alt="Snow sprint">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex gap-3">
                                <button>❤️ 152</button>
                                <button>💬 21</button>
                                <button>🔁 6</button>
                            </div>
                            <button>Share</button>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
                        <div class="flex items-center gap-3">
                            <img src="https://placekitten.com/96/96" class="h-12 w-12 rounded-full" alt="Post avatar">
                            <div>
                                <p class="font-semibold">Alex Johnson</p>
                                <p class="text-xs text-slate-500">Blog · 6 min read</p>
                            </div>
                        </div>
                        <h4 class="text-lg font-semibold">5 Winter Safety Tips for Adventure Pets</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-300">From reflective harnesses to paw balm, keep your companion safe during winter adventures.</p>
                        <button class="text-sm text-indigo-600">Continue reading</button>
                    </div>
                </div>
            </div>
        </article>

        <!-- Explore surfaces discovery with facets, tabs, and follow CTAs. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Explore · Search, Tabs, Facets <span class="text-base">(M, M)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Unified search integrates trending tags, grid/list toggles, and inline follow buttons.</p>
            </header>
            <div class="space-y-6">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex items-center gap-3 rounded-full border border-slate-200 px-4 py-2 flex-1 min-w-[240px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7 7 0 1010.3 17l4.35 4.35z" />
                        </svg>
                        <input type="text" placeholder="Search pets, posts, blogs" class="flex-1 bg-transparent">
                    </div>
                    <div class="flex gap-2 text-sm">
                        <button class="px-3 py-1 rounded-full bg-indigo-600 text-white">Grid</button>
                        <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">List</button>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
                    <button class="px-3 py-1 rounded-full bg-indigo-600 text-white">Top</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">People</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Pets</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Tags</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Events</button>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 space-y-2 text-center">
                        <img src="https://placekitten.com/130/130" class="mx-auto h-16 w-16 rounded-full" alt="Explore pet">
                        <p class="font-semibold">Atlas the Samoyed</p>
                        <p class="text-xs text-slate-500">Snow hikes · Alaska</p>
                        <button class="px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Follow</button>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 space-y-2 text-center">
                        <img src="https://placekitten.com/131/131" class="mx-auto h-16 w-16 rounded-full" alt="Explore pet">
                        <p class="font-semibold">Marley the Cat</p>
                        <p class="text-xs text-slate-500">Indoor enrichment · Seattle</p>
                        <button class="px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Follow</button>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 space-y-2 text-center">
                        <img src="https://placekitten.com/132/132" class="mx-auto h-16 w-16 rounded-full" alt="Explore pet">
                        <p class="font-semibold">Bowie the Parrot</p>
                        <p class="text-xs text-slate-500">Trick training · Portland</p>
                        <button class="px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Follow</button>
                    </div>
                </div>
            </div>
        </article>
        <!-- User profile highlights cover actions, tabs, and editing. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Profile (User) · Cover, Tabs, Edit Modal <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Profile pages blend hero imagery with quick access to posts, blogs, and pets.</p>
            </header>
            <div class="rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-700">
                <div class="relative h-48 bg-gradient-to-r from-indigo-500 to-purple-500">
                    <button class="absolute top-4 right-4 px-3 py-1 rounded-full bg-white/80 text-sm">Edit cover</button>
                    <div class="absolute bottom-4 left-6 flex items-end gap-4">
                        <img src="https://placekitten.com/160/160" class="h-24 w-24 rounded-full border-4 border-white" alt="Profile avatar">
                        <div class="text-white">
                            <h4 class="text-2xl font-semibold">Alex Johnson</h4>
                            <p class="text-sm">Pet adventurer · Anchorage, AK</p>
                        </div>
                    </div>
                    <div class="absolute bottom-4 right-6 flex gap-3">
                        <button class="px-4 py-2 rounded-full bg-white text-indigo-600">Follow</button>
                        <button class="px-4 py-2 rounded-full bg-white/30 text-white border border-white/40">Message</button>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <div class="flex flex-wrap gap-3 text-sm">
                        <button class="px-3 py-1 rounded-full bg-indigo-600 text-white">Posts</button>
                        <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Blogs</button>
                        <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Pets</button>
                        <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">About</button>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                        <h4 class="font-semibold">About</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-300">Outdoor explorer with a passion for avalanche rescue dogs.</p>
                    </div>
                    <button class="px-4 py-2 rounded-full border border-slate-300">Edit profile</button>
                </div>
            </div>
        </article>

        <!-- Pet profile centralises pet-specific facts and owner controls. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Pet · Cover, Quick Facts, Owner Controls <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Pet hubs display timeline & gallery tabs alongside owner-only management.</p>
            </header>
            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <div class="rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-700">
                        <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80" class="h-56 w-full object-cover" alt="Pet cover">
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-2xl font-semibold">Nova the Husky</h4>
                                    <p class="text-sm text-slate-500">Sled dog · 4 years old</p>
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-3 py-1 rounded-full bg-indigo-600 text-white">Follow pet</button>
                                    <button class="px-3 py-1 rounded-full border border-slate-300">Message owner</button>
                                </div>
                            </div>
                            <div class="flex gap-3 text-sm">
                                <button class="px-3 py-1 rounded-full bg-indigo-600 text-white">Timeline</button>
                                <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Gallery</button>
                                <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Stats</button>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                        <h4 class="font-semibold">Owner Controls</h4>
                        <p class="text-sm text-slate-500">Quick actions for editing pet details, updating medical records, and managing followers.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button class="px-3 py-1 rounded-full border border-slate-300">Edit pet</button>
                            <button class="px-3 py-1 rounded-full border border-slate-300">Update medical records</button>
                            <button class="px-3 py-1 rounded-full border border-slate-300">Share profile</button>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                        <h4 class="font-semibold">Quick Facts</h4>
                        <ul class="text-sm text-slate-500 space-y-2">
                            <li>Breed: Siberian Husky</li>
                            <li>Weight: 45 lbs</li>
                            <li>Favourite activity: Backcountry trails</li>
                            <li>Veterinary clinic: Mountain Ridge</li>
                        </ul>
                    </div>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                        <h4 class="font-semibold">Owner</h4>
                        <div class="flex items-center gap-3">
                            <img src="https://placekitten.com/125/125" class="h-12 w-12 rounded-full" alt="Owner avatar">
                            <div>
                                <p class="font-semibold">Alex Johnson</p>
                                <button class="text-xs text-indigo-600">View profile</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Blog layout balances index and immersive reading. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Blog · Index & Reader <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Searchable index with tags complements a rich reader view with table of contents and progress.</p>
            </header>
            <div class="grid lg:grid-cols-[320px,1fr] gap-6">
                <div class="space-y-4">
                    <input type="search" placeholder="Search blogs" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <select class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option>Newest</option>
                        <option>Most liked</option>
                        <option>Most commented</option>
                    </select>
                    <div class="space-y-2">
                        <span class="text-xs font-semibold uppercase text-slate-500">Tags</span>
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Training</span>
                            <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Nutrition</span>
                            <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Rescue</span>
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-indigo-600">Reading time · 6 min</span>
                            <span>Updated 1 day ago</span>
                        </div>
                        <h4 class="text-2xl font-semibold">How to Prep Your Pet for Winter Adventures</h4>
                        <div class="flex gap-4 text-sm">
                            <div class="w-1/4 space-y-2">
                                <p class="font-semibold">Table of Contents</p>
                                <ol class="text-xs space-y-1 list-decimal list-inside">
                                    <li>Gear checklist</li>
                                    <li>Trail safety</li>
                                    <li>Post-adventure care</li>
                                </ol>
                                <div class="mt-4">
                                    <p class="font-semibold text-xs">Reading Progress</p>
                                    <div class="h-1 w-full bg-slate-200 rounded-full mt-2">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: 40%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 space-y-3">
                                <p class="text-sm text-slate-600 dark:text-slate-300">Cold weather outings demand thoughtful preparation to keep your pet safe, comfortable, and excited for the next adventure.</p>
                                <blockquote class="border-l-4 border-indigo-500 pl-4 text-sm">“Always carry a space blanket and booties for emergencies.”</blockquote>
                                <div class="rounded-xl bg-slate-100 dark:bg-slate-800 p-4 text-sm">
                                    <p><strong>Pro tip:</strong> Pack high-calorie treats to maintain energy.</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <button class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700">👏 124</button>
                            <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">💬 32</button>
                            <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Share</button>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        <!-- Wiki patterns emphasise discovery and related content. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Wiki · Category Tabs + Reader <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Structured knowledge base with searchable categories and related articles.</p>
            </header>
            <div class="space-y-6">
                <div class="flex flex-wrap gap-2 text-sm">
                    <button class="px-3 py-1 rounded-full bg-indigo-600 text-white">Care</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Training</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Behavior</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Nutrition</button>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xl font-semibold">Cold Weather Gear Checklist</h4>
                        <input type="search" placeholder="Search wiki" class="rounded-full border border-slate-300 px-3 py-1 text-sm">
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Ensure your pet stays warm and safe with these essentials.</p>
                    <div class="rounded-xl bg-slate-100 dark:bg-slate-800 p-4 text-sm space-y-2">
                        <p><strong>Layering:</strong> Waterproof outerwear plus insulated mid-layers.</p>
                        <p><strong>Visibility:</strong> Reflective harness and leash accessories.</p>
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold">Related articles</p>
                        <ul class="list-disc list-inside text-slate-500 space-y-1">
                            <li>Snowpack safety basics</li>
                            <li>Paw care in freezing temps</li>
                            <li>Emergency kits for trail rides</li>
                        </ul>
                    </div>
                </div>
            </div>
        </article>

        <!-- Notifications support filtering, unread states, and bulk actions. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8" x-data="{ filter: 'All' }">
            <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold">Notifications · Filters & Bulk Actions <span class="text-base">(M, S)</span></h3>
                    <p class="text-slate-600 dark:text-slate-300">Readable list with unread indicators, quick filters, and mark-as-read controls.</p>
                </div>
                <div class="flex gap-2 text-sm">
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Mark all read</button>
                    <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Notification settings</button>
                </div>
            </header>
            <div class="flex flex-wrap gap-2 text-sm">
                <template x-for="option in ['All', 'Likes', 'Comments', 'Follows']" :key="option">
                    <button @click="filter = option" class="px-3 py-1 rounded-full" :class="filter === option ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800'" x-text="option"></button>
                </template>
            </div>
            <div class="space-y-3">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 flex items-start gap-3 bg-indigo-50 dark:bg-indigo-500/10">
                    <span class="h-3 w-3 rounded-full bg-indigo-600 mt-1"></span>
                    <div>
                        <p class="text-sm"><strong>Mochi the Cat</strong> reacted to your photo.</p>
                        <p class="text-xs text-slate-500">2 minutes ago</p>
                    </div>
                    <button class="ml-auto text-xs text-indigo-600">Mark read</button>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 flex items-start gap-3">
                    <span class="h-3 w-3 rounded-full bg-slate-300 mt-1"></span>
                    <div>
                        <p class="text-sm"><strong>Alex Johnson</strong> commented on your blog.</p>
                        <p class="text-xs text-slate-500">1 hour ago</p>
                    </div>
                </div>
            </div>
        </article>

        <!-- Settings groups account preferences and danger zone. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Settings · Tabs & Danger Zone <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Segment profile, privacy, notification, and appearance settings with a clearly marked danger zone.</p>
            </header>
            <div class="grid lg:grid-cols-[240px,1fr] gap-6">
                <nav class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 space-y-2">
                    <button class="w-full text-left px-3 py-2 rounded-lg bg-indigo-600 text-white">Profile</button>
                    <button class="w-full text-left px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800">Account</button>
                    <button class="w-full text-left px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800">Privacy</button>
                    <button class="w-full text-left px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800">Notifications</button>
                    <button class="w-full text-left px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800">Appearance</button>
                </nav>
                <div class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
                        <h4 class="font-semibold">Profile Settings</h4>
                        <p class="text-sm text-slate-500">Update bio, location, and pronouns with inline previews.</p>
                        <button class="px-3 py-1 rounded-full bg-indigo-600 text-white text-sm">Save changes</button>
                    </div>
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-500/10 dark:border-rose-500 p-5 space-y-3">
                        <h4 class="font-semibold text-rose-600">Danger Zone</h4>
                        <p class="text-sm text-rose-500">Delete your account and permanently remove content.</p>
                        <button class="px-3 py-1 rounded-full bg-rose-500 text-white text-sm">Delete account</button>
                    </div>
                </div>
            </div>
        </article>

        <!-- Reporting and moderation workflows capture confirmation feedback. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">Reporting & Moderation · Modal + Thank-you Toast <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Overflow menus trigger report modals, followed by confirmation toasts.</p>
            </header>
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
                    <h4 class="font-semibold">Overflow Menu</h4>
                    <p class="text-sm text-slate-500">Report, mute, and block actions remain one tap away.</p>
                    <button class="px-4 py-2 rounded-lg border border-slate-300">Open report modal</button>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5 space-y-3">
                    <h4 class="font-semibold">Confirmation Toast</h4>
                    <p class="text-sm text-slate-500">Acknowledges the report and thanks the member for keeping the community safe.</p>
                    <div class="px-4 py-3 rounded-xl bg-emerald-100 text-emerald-700 text-sm">Thanks! Our moderators will review this within 24 hours.</div>
                </div>
            </div>
        </article>

        <!-- System pages reassure during errors or missing routes. -->
        <article class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-xl space-y-8">
            <header>
                <h3 class="text-2xl font-bold">System · 404 & Error Boundary <span class="text-base">(M, S)</span></h3>
                <p class="text-slate-600 dark:text-slate-300">Friendly illustrations and retry actions maintain trust during failures.</p>
            </header>
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-6 text-center space-y-3">
                    <h4 class="text-xl font-semibold">404 · “We lost this pet!”</h4>
                    <p class="text-sm text-slate-500">Sorry, we couldn’t find that page. Try searching again or head back home.</p>
                    <button class="px-4 py-2 rounded-full bg-indigo-600 text-white">Return home</button>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-6 text-center space-y-3">
                    <h4 class="text-xl font-semibold">Error Boundary</h4>
                    <p class="text-sm text-slate-500">Something went wrong while fetching this content.</p>
                    <div class="flex justify-center gap-2 text-sm">
                        <button class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800">Retry</button>
                        <button class="px-3 py-1 rounded-full border border-slate-300">Contact support</button>
                    </div>
                </div>
            </div>
        </article>
    </section>
@endsection
