<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chatbot Starter Pack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between font-sans antialiased">

    <!-- Top Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-10 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/30">
                🤖
            </div>
            <div>
                <h1 class="text-sm font-semibold tracking-wide">AI Starter Pack</h1>
                <p class="text-xs text-slate-400">Powered by 9Router & Function Calling</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Siap Digunakan
        </span>
    </header>

    <!-- Chat Container -->
    <main x-data="chatApp()" class="flex-1 max-w-3xl w-full mx-auto p-4 flex flex-col justify-between">
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto space-y-4 pb-4" id="messages-container">
            <!-- Welcome Message -->
            <template x-if="messages.length === 0">
                <div class="py-12 text-center space-y-4">
                    <div class="inline-flex p-4 rounded-2xl bg-slate-800/60 border border-slate-700/50 text-3xl">
                        💬
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">Ada yang bisa dibantu?</h2>
                        <p class="text-sm text-slate-400 max-w-md mx-auto mt-1">
                            Chatbot ini terhubung langsung dengan action dan database. Coba tanyakan pertanyaan berikut:
                        </p>
                    </div>

                    <!-- Quick Prompt Suggestions -->
                    <div class="flex flex-wrap justify-center gap-2 pt-2">
                        <button @click="sendPrompt('Berapa jumlah total siswa?')" class="text-xs bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-full px-3 py-1.5 transition text-slate-300">
                            📊 "Berapa jumlah total siswa?"
                        </button>
                        <button @click="sendPrompt('Ada berapa siswa di jurusan RPL?')" class="text-xs bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-full px-3 py-1.5 transition text-slate-300">
                            💻 "Ada berapa siswa di jurusan RPL?"
                        </button>
                        <button @click="sendPrompt('Halo! Kamu siapa?')" class="text-xs bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-full px-3 py-1.5 transition text-slate-300">
                            👋 "Halo! Kamu siapa?"
                        </button>
                    </div>
                </div>
            </template>

            <!-- Chat Bubble List -->
            <template x-for="(msg, index) in displayMessages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' 
                        ? 'bg-indigo-600 text-white rounded-2xl rounded-br-sm px-4 py-2.5 max-w-[85%] text-sm leading-relaxed shadow' 
                        : 'bg-slate-800 border border-slate-700/70 text-slate-200 rounded-2xl rounded-bl-sm px-4 py-2.5 max-w-[85%] text-sm leading-relaxed whitespace-pre-line'">
                        <span x-text="msg.content"></span>
                    </div>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="loading" class="flex justify-start">
                <div class="bg-slate-800 border border-slate-700/70 rounded-2xl rounded-bl-sm px-4 py-3 flex items-center space-x-1.5">
                    <div class="w-2 h-2 rounded-full bg-slate-400 animate-bounce"></div>
                    <div class="w-2 h-2 rounded-full bg-slate-400 animate-bounce [animation-delay:0.2s]"></div>
                    <div class="w-2 h-2 rounded-full bg-slate-400 animate-bounce [animation-delay:0.4s]"></div>
                </div>
            </div>
        </div>

        <!-- Input Box -->
        <div class="pt-2 sticky bottom-4">
            <form @submit.prevent="sendMessage()" class="relative flex items-center">
                <input 
                    type="text" 
                    x-model="inputMessage" 
                    :disabled="loading"
                    placeholder="Ketik pertanyaanmu di sini..." 
                    class="w-full bg-slate-800/90 border border-slate-700 rounded-2xl pl-4 pr-12 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-slate-500 disabled:opacity-50 transition shadow-lg"
                >
                <button 
                    type="submit" 
                    :disabled="loading || !inputMessage.trim()" 
                    class="absolute right-2 p-2 bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-700 disabled:cursor-not-allowed text-white rounded-xl transition shadow flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </form>
        </div>
    </main>

    <script>
        function chatApp() {
            return {
                inputMessage: '',
                loading: false,
                messages: [], // internal conversation history
                displayMessages: [], // only user & assistant text for UI

                sendPrompt(text) {
                    this.inputMessage = text;
                    this.sendMessage();
                },

                async sendMessage() {
                    const text = this.inputMessage.trim();
                    if (!text || this.loading) return;

                    // Tambahkan ke UI
                    this.displayMessages.push({ role: 'user', content: text });
                    this.inputMessage = '';
                    this.loading = true;
                    this.scrollToBottom();

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/api/chat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                message: text,
                                history: this.messages
                            })
                        });

                        const data = await res.json();

                        if (data.reply) {
                            this.displayMessages.push({ role: 'assistant', content: data.reply });
                            this.messages = data.history || [];
                        } else {
                            this.displayMessages.push({ role: 'assistant', content: 'Maaf, ada kendala: ' + (data.message || 'Tidak ada balasan') });
                        }
                    } catch (err) {
                        this.displayMessages.push({ role: 'assistant', content: 'Gagal terhubung ke server. Periksa koneksi dan pengaturan .env kamu.' });
                    } finally {
                        this.loading = false;
                        this.scrollToBottom();
                    }
                },

                scrollToBottom() {
                    setTimeout(() => {
                        const container = document.getElementById('messages-container');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    }, 50);
                }
            }
        }
    </script>
</body>
</html>
