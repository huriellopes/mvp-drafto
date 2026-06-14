import './bootstrap';
import '../../vendor/masmerise/livewire-toaster/resources/js';

const applyTheme = () => {
    if (!document.documentElement.hasAttribute('data-site-theme')) {
        document.documentElement.classList.remove('dark');
        document.body.classList.remove('dark'); // Extra safety
        return;
    }

    const theme = localStorage.getItem('theme');
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

document.addEventListener('livewire:navigated', applyTheme);
applyTheme();

// Analytics Tracking
document.addEventListener('alpine:init', () => {
    Alpine.data('analyticsTracking', () => ({
        startTime: Date.now(),
        url: window.location.href,

        init() {
            // Heartbeat every 20 seconds
            this.timer = setInterval(() => this.sendDuration(), 20000);

            // Send on visibility change (better than beforeunload in mobile)
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    this.sendDuration(true);
                }
            });
        },

        destroy() {
            if (this.timer) clearInterval(this.timer);
        },

        sendDuration(isBeacon = false) {
            const duration = Math.floor((Date.now() - this.startTime) / 1000);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!token) return;

            const data = {
                url: this.url,
                duration: duration
            };

            if (isBeacon && navigator.sendBeacon) {
                const formData = new FormData();
                formData.append('url', data.url);
                formData.append('duration', data.duration);
                formData.append('_token', token);
                navigator.sendBeacon('/analytics/duration', formData);
            } else {
                fetch('/analytics/duration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                }).catch(() => {});
            }
        }
    }));
});

// Immediate cleanup for dashboard if any leak occurs
if (!document.documentElement.hasAttribute('data-site-theme')) {
    document.documentElement.classList.remove('dark');
}

/* ---------------------------------------------------------------------------
 | Quill Rich Editor
 |
 | Registramos o componente Alpine e um blot de vídeo customizado uma única
 | vez. O blot baseado na tag <video> garante que o upload de vídeo seja
 | renderizado, salvo e recuperado corretamente ao reabrir o editor (algo que
 | o blot padrão do Quill, baseado em <iframe>, não faz com arquivos locais).
 |
 | @see https://quilljs.com/docs/guides/cloning-medium-with-parchment
 --------------------------------------------------------------------------- */
const registerQuillVideoBlot = () => {
    if (typeof window.Quill === 'undefined' || window.__quillVideoBlotRegistered) return;
    window.__quillVideoBlotRegistered = true;

    const BlockEmbed = window.Quill.import('blots/block/embed');

    // Blot para vídeo ENVIADO do computador: tag <video> nativa.
    class VideoBlot extends BlockEmbed {
        static blotName = 'video';
        static tagName = 'video';

        static create(value) {
            const node = super.create();
            const url = value && typeof value === 'object' ? value.url : value;

            node.setAttribute('src', url);
            node.setAttribute('controls', '');
            node.setAttribute('controlslist', 'nodownload');
            node.setAttribute('preload', 'metadata');
            node.setAttribute('playsinline', '');

            return node;
        }

        static value(node) {
            return node.getAttribute('src');
        }
    }

    // Blot para vídeo EMBUTIDO via link (YouTube/Vimeo): <iframe class="ql-video">.
    class VideoEmbedBlot extends BlockEmbed {
        static blotName = 'videoEmbed';
        static tagName = 'iframe';
        static className = 'ql-video';

        static create(value) {
            const node = super.create();
            node.setAttribute('src', value);
            node.setAttribute('frameborder', '0');
            node.setAttribute('allowfullscreen', 'true');
            node.setAttribute(
                'allow',
                'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
            );
            return node;
        }

        static value(node) {
            return node.getAttribute('src');
        }
    }

    window.Quill.register(VideoBlot, true);
    window.Quill.register(VideoEmbedBlot);
};

document.addEventListener('alpine:init', () => {
    registerQuillVideoBlot();

    Alpine.data('quillEditor', ({ model, uploadUrl, placeholder }) => ({
        quill: null,
        // Nome da propriedade Livewire (ex.: "form.content"). Escrevemos via
        // $wire diretamente — passar @entangle pela factory quebra o binding.
        model,
        uploadUrl,
        placeholder,
        uploading: false,
        uploadLabel: 'Processando',
        // Última posição conhecida do cursor (atualizada via selection-change).
        // Usar getSelection() no clique do toolbar lança erro quando o editor
        // perdeu o foco para o botão, então rastreamos a seleção continuamente.
        savedRange: null,
        // Estado do modal de vídeo por link (x-ui.modal).
        videoUrl: '',
        videoError: '',

        init() {
            registerQuillVideoBlot();

            this.quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: this.placeholder,
                modules: {
                    toolbar: {
                        container: [
                            [{ header: [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ color: [] }, { background: [] }],
                            [{ list: 'ordered' }, { list: 'bullet' }, { align: [] }],
                            ['blockquote', 'code-block'],
                            ['link', 'image', 'video'],
                            ['clean'],
                        ],
                        handlers: {
                            image: () => this.uploadFile('image'),
                            video: () => this.insertVideo(),
                        },
                    },
                },
            });

            // Quill 2.0.3 lança "Cannot read properties of null (reading
            // 'offset')" em selection.getRange()/update() quando a seleção nativa
            // aponta para fora do editor — o que acontece ao clicar num botão do
            // toolbar SEM antes clicar no editor (caso real na edição). Isso
            // impedia os botões de imagem/vídeo de funcionarem. Blindamos esses
            // dois caminhos: são inofensivos (o conteúdo continua íntegro).
            const selection = this.quill.selection;
            if (selection) {
                if (typeof selection.getRange === 'function') {
                    const originalGetRange = selection.getRange.bind(selection);
                    selection.getRange = (...args) => {
                        try {
                            return originalGetRange(...args);
                        } catch (e) {
                            return [null, null];
                        }
                    };
                }
                if (typeof selection.update === 'function') {
                    const originalUpdate = selection.update.bind(selection);
                    selection.update = (...args) => {
                        try {
                            return originalUpdate(...args);
                        } catch (e) {
                            /* seleção fora do editor: ignora com segurança */
                        }
                    };
                }
                // setNativeRange faz range.setStart(); com um índice inválido
                // (ex.: após insert programático) lança "offset ... larger than
                // node's length" e travava o PRÓXIMO clique no toolbar.
                if (typeof selection.setNativeRange === 'function') {
                    const originalSetNativeRange = selection.setNativeRange.bind(selection);
                    selection.setNativeRange = (...args) => {
                        try {
                            return originalSetNativeRange(...args);
                        } catch (e) {
                            /* range inválido: ignora com segurança */
                        }
                    };
                }
                // getBounds também faz range.setStart() (para medir posição).
                // focus() -> scrollSelectionIntoView() -> getBounds() lança com
                // seleção inválida após inserir um embed de bloco (vídeo); null
                // faz o Quill pular o scroll com segurança.
                if (typeof selection.getBounds === 'function') {
                    const originalGetBounds = selection.getBounds.bind(selection);
                    selection.getBounds = (...args) => {
                        try {
                            return originalGetBounds(...args);
                        } catch (e) {
                            return null;
                        }
                    };
                }
            }

            // Carga inicial do conteúdo existente (edição). Fonte 'silent' para
            // não emitir text-change nem disparar a sincronização de seleção do
            // Quill (que lança quando o editor ainda não recebeu foco).
            const initial = this.$wire.get(this.model);
            if (initial) {
                const sel = window.getSelection();
                if (sel) sel.removeAllRanges();
                this.quill.clipboard.dangerouslyPasteHTML(0, initial, 'silent');
            }

            // Editor -> Livewire
            this.quill.on('text-change', (_delta, _oldDelta, source) => {
                if (source === 'user') {
                    this.syncToLivewire();
                }
            });

            // Rastreia a posição do cursor para inserir embeds no lugar certo.
            this.quill.on('selection-change', (range) => {
                if (range) this.savedRange = range;
            });

            // Garante que <iframe> de vídeo (YouTube/Vimeo) existentes sejam
            // reconhecidos ao recarregar o conteúdo, em vez de descartados.
            const Delta = window.Quill.import('delta');
            this.quill.clipboard.addMatcher('IFRAME', (node, delta) => {
                const src = node.getAttribute('src');
                return src ? new Delta().insert({ videoEmbed: src }) : delta;
            });
        },

        syncToLivewire() {
            let html = this.quill.getSemanticHTML();
            if (html === '<p></p>') html = '';

            // Escrita diferida (false): não dispara request a cada tecla; o valor
            // segue junto no próximo commit (salvar/publicar ou autosave de outro
            // campo), exatamente como o wire:model deferido original.
            this.$wire.set(this.model, html, false);
        },

        /**
         * Insere um embed (image / video / videoEmbed) na última posição
         * conhecida do cursor, de forma resiliente à perda de foco do editor.
         */
        insertEmbedAt(embedType, value) {
            // Índice de inserção: última posição conhecida do cursor, limitado ao
            // tamanho atual do documento (evita offsets inválidos no Quill).
            const length = this.quill.getLength();
            let index = this.savedRange ? this.savedRange.index : length - 1;
            index = Math.max(0, Math.min(index, length - 1));

            try {
                // Dar foco e uma seleção VÁLIDA antes de inserir mantém o estado
                // interno do Quill consistente.
                this.quill.focus();
                this.quill.setSelection(index, 0);
                this.quill.insertEmbed(index, embedType, value);
                this.quill.setSelection(index + 1, 0);
                this.savedRange = { index: index + 1, length: 0 };
            } catch (e) {
                // O Parchment do Quill pode dessincronizar em certos estados do
                // documento (erro "insertBefore ... not a child"). Recupera
                // reconstruindo o conteúdo a partir do HTML atual + o embed,
                // garantindo que o vídeo/imagem seja SEMPRE inserido.
                console.warn('insertEmbed fallback:', e?.message);
                this.appendEmbedHtml(embedType, value);
            }

            this.syncToLivewire();
        },

        /** Recuperação resiliente: anexa o embed ao final do conteúdo atual. */
        appendEmbedHtml(embedType, value) {
            const html =
                embedType === 'video'
                    ? `<video src="${value}" controls controlslist="nodownload" preload="metadata" playsinline></video>`
                    : embedType === 'videoEmbed'
                      ? `<iframe class="ql-video" src="${value}" frameborder="0" allowfullscreen></iframe>`
                      : `<img src="${value}">`;

            let current = this.quill.getSemanticHTML();
            if (current === '<p></p>') current = '';

            this.quill.setContents([]);
            this.quill.clipboard.dangerouslyPasteHTML(0, current + html, 'silent');
            this.savedRange = { index: this.quill.getLength() - 1, length: 0 };
        },

        /**
         * Botão de vídeo: abre o modal (x-ui.modal) para colar um link de
         * YouTube/Vimeo ou enviar um arquivo do computador.
         */
        insertVideo() {
            this.videoUrl = '';
            this.videoError = '';
            this.$dispatch('open-modal', { name: 'quill-video-link' });
        },

        /** Confirma o link de vídeo digitado no modal. */
        confirmVideoLink() {
            const value = (this.videoUrl || '').trim();
            if (value === '') {
                this.videoError = 'Cole um link do YouTube ou Vimeo.';
                return;
            }

            const embedUrl = this.normalizeVideoUrl(value);
            if (!embedUrl) {
                this.videoError = 'Link inválido. Use um endereço do YouTube ou Vimeo.';
                return;
            }

            this.insertEmbedAt('videoEmbed', embedUrl);
            this.videoUrl = '';
            this.videoError = '';
            this.$dispatch('close-modal', { name: 'quill-video-link' });
        },

        /** Botão do modal: fechar e enviar um vídeo do computador. */
        uploadVideoFromModal() {
            this.$dispatch('close-modal', { name: 'quill-video-link' });
            // Adia a abertura do seletor de arquivos para o próximo tick: abrir
            // o diálogo no mesmo tick impede o Alpine de processar o fechamento
            // do modal (ele ficaria aberto sobre o editor).
            setTimeout(() => this.uploadFile('video'), 60);
        },

        /**
         * Converte links comuns (ou um código <iframe> colado) na URL de embed
         * aceita pelo sanitizador. Retorna null se não for YouTube/Vimeo.
         */
        normalizeVideoUrl(raw) {
            // Se colaram o código <iframe ... src="...">, extrai o src.
            const iframeMatch = raw.match(/<iframe[^>]*\ssrc=["']([^"']+)["']/i);
            const url = iframeMatch ? iframeMatch[1] : raw;

            const yt = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/);
            if (yt) return `https://www.youtube.com/embed/${yt[1]}`;

            const vimeo = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
            if (vimeo) return `https://player.vimeo.com/video/${vimeo[1]}`;

            return null;
        },

        /**
         * Abre o seletor de arquivos, faz o upload e insere o embed
         * (imagem ou vídeo) na posição atual do cursor.
         */
        uploadFile(type) {
            const inputEl = document.createElement('input');
            inputEl.type = 'file';
            inputEl.accept = type === 'video' ? 'video/*' : 'image/*';

            inputEl.onchange = async () => {
                const file = inputEl.files[0];
                if (!file) return;

                this.uploading = true;
                this.uploadLabel = type === 'video' ? 'Enviando Vídeo' : 'Processando Imagem';

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch(this.uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    if (!response.ok) throw new Error('Falha no upload');

                    const data = await response.json();
                    const embedType = data.type === 'video' ? 'video' : 'image';

                    this.insertEmbedAt(embedType, data.url);
                } catch (error) {
                    console.error('Upload Error:', error);
                    alert('Não foi possível enviar o arquivo. Verifique o tamanho e o formato.');
                } finally {
                    this.uploading = false;
                }
            };

            inputEl.click();
        },
    }));
});
