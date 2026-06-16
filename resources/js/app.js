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
if (typeof window !== 'undefined' && !window.__quillErrorSilencerInstalled) {
    window.__quillErrorSilencerInstalled = true;
    window.addEventListener(
        'error',
        (event) => {
            const fromQuill = (event.filename || '').includes('quill');
            const msg = (event.message || '').toLowerCase();
            const isSelectionBug =
                msg.includes("reading 'offset'") ||
                msg.includes('setstart') ||
                (msg.includes('offset') && msg.includes('4294967295'));
            if (fromQuill && isSelectionBug) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        },
        true,
    );
}

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

    Alpine.data('quillEditor', ({ model, uploadUrl, placeholder, maxUploadKb }) => ({
        quill: null,
        model,
        uploadUrl,
        placeholder,
        // Limite de upload (KB) vindo do servidor (config/editor.php) — fonte única.
        maxUploadKb: maxUploadKb || 102400,
        uploading: false,
        uploadLabel: 'Processando',
        savedRange: null,
        videoUrl: '',
        videoError: '',
        linkUrl: '',
        linkText: '',
        linkError: '',
        linkRange: null,
        errorMessage: '',
        guardSelection: null,
        unguardSelection: null,

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

            // Contorno PONTUAL de um bug do Quill 2.0.3: getRange()/getBounds()
            // lançam quando o editor está sem foco — situação que só ocorre ao
            // INSERIR mídia logo após o diálogo de arquivo/modal. As proteções
            // são INSTALADAS só durante a inserção e REMOVIDAS logo depois. Na
            // edição normal os métodos do Quill são os originais (sem wrapper),
            // então digitar/apagar/enter/colar funcionam 100% livres — com ou
            // sem imagem/vídeo no conteúdo.
            const sel = this.quill.selection;
            if (sel) {
                const origGetRange = sel.getRange.bind(sel);
                const origGetBounds = sel.getBounds.bind(sel);
                const origSetNativeRange = sel.setNativeRange.bind(sel);
                this.guardSelection = () => {
                    sel.getRange = (...a) => {
                        try {
                            return origGetRange(...a);
                        } catch (e) {
                            return [sel.lastRange || { index: Math.max(0, this.quill.getLength() - 1), length: 0 }, null];
                        }
                    };
                    sel.getBounds = (...a) => {
                        try { return origGetBounds(...a); } catch (e) { return null; }
                    };
                    // setNativeRange faz range.setStart(); com índice inválido
                    // (estado pós-apagar mídia) lança "offset ... is invalid".
                    sel.setNativeRange = (...a) => {
                        try { return origSetNativeRange(...a); } catch (e) { /* ignore */ }
                    };
                };
                this.unguardSelection = () => {
                    delete sel.getRange;
                    delete sel.getBounds;
                    delete sel.setNativeRange;
                };
            }

            // Clicar nos botões de imagem/vídeo do toolbar dispara um handler
            // INTERNO do Quill (focus + getRange/setSelection) ANTES do nosso —
            // e ele lança quando a seleção está inválida (ex.: logo após apagar
            // uma imagem), fazendo o botão "não responder" (a janela não abre).
            // Interceptamos o clique em CAPTURA e chamamos nosso handler direto,
            // impedindo o handler problemático do Quill de rodar.
            const toolbar = this.quill.getModule('toolbar');
            const toolbarEl = toolbar?.container;
            if (toolbarEl) {
                const intercept = (selector, fn) => {
                    const btn = toolbarEl.querySelector(selector);
                    if (!btn) return;
                    btn.addEventListener(
                        'click',
                        (e) => {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            fn();
                        },
                        true,
                    );
                };
                intercept('.ql-image', () => this.uploadFile('image'));
                intercept('.ql-video', () => this.insertVideo());
                // O link nativo do Quill só funciona com texto selecionado e
                // quebra sem foco. Usamos um modal que funciona em qualquer caso.
                intercept('.ql-link', () => this.insertLink());
            }

            this.quill.on('text-change', (_delta, _oldDelta, source) => {
                if (source === 'user') {
                    this.syncToLivewire();
                }
            });

            this.quill.on('selection-change', (range) => {
                if (range) this.savedRange = range;
            });

            const Delta = window.Quill.import('delta');
            this.quill.clipboard.addMatcher('IFRAME', (node, delta) => {
                const src = node.getAttribute('src');
                return src ? new Delta().insert({ videoEmbed: src }) : delta;
            });
        },

        syncToLivewire() {
            let html = this.quill.getSemanticHTML();
            if (html === '<p></p>') html = '';

            this.$wire.set(this.model, html, false);
        },

        /**
         * Insere um embed (image / video / videoEmbed) na última posição
         * conhecida do cursor, de forma resiliente à perda de foco do editor.
         */
        insertEmbedAt(embedType, value) {
            const length = this.quill.getLength();
            let index = this.savedRange ? this.savedRange.index : length - 1;
            index = Math.max(0, Math.min(index, length - 1));

            // Instala a proteção SÓ durante a inserção (editor sem foco após
            // diálogo/modal). Reativa o foco e insere no cursor.
            if (this.guardSelection) this.guardSelection();
            try {
                this.quill.getSelection(true); // foca o editor (documentado)
                this.quill.insertEmbed(index, embedType, value, 'user');
                this.quill.setSelection(index + 1, 0);
                this.savedRange = { index: index + 1, length: 0 };
            } finally {
                // Remove a proteção após o ciclo de update assíncrono do Quill,
                // voltando à edição totalmente livre (sem wrappers).
                if (this.unguardSelection) setTimeout(this.unguardSelection, 200);
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

            // Fecha o modal ANTES de inserir, para o editor não disputar foco com
            // o overlay do modal (o que gerava um erro de seleção na inserção).
            this.videoUrl = '';
            this.videoError = '';
            this.$dispatch('close-modal', { name: 'quill-video-link' });
            setTimeout(() => this.insertEmbedAt('videoEmbed', embedUrl), 60);
        },

        /**
         * Botão de link: abre o modal para inserir/aplicar um link. Guarda a
         * seleção atual (texto selecionado) para aplicar o link nela.
         */
        insertLink() {
            // Captura a seleção atual (texto selecionado) ANTES de abrir o modal.
            const length = this.quill.getLength();
            this.linkRange = this.savedRange
                ? { index: this.savedRange.index, length: this.savedRange.length }
                : { index: Math.max(0, length - 1), length: 0 };
            this.linkUrl = '';
            this.linkText = '';
            this.linkError = '';
            this.$dispatch('open-modal', { name: 'quill-link' });
        },

        /** Confirma o link do modal: aplica à seleção ou insere no cursor. */
        confirmLink() {
            let url = (this.linkUrl || '').trim();
            if (url === '') {
                this.linkError = 'Informe a URL do link.';
                return;
            }
            // Normaliza: adiciona https:// se não houver protocolo (exceto mailto).
            if (!/^(https?:\/\/|mailto:|\/)/i.test(url)) {
                url = 'https://' + url;
            }

            const range = this.linkRange || { index: Math.max(0, this.quill.getLength() - 1), length: 0 };
            const text = (this.linkText || '').trim();

            this.linkUrl = '';
            this.linkText = '';
            this.linkError = '';
            this.$dispatch('close-modal', { name: 'quill-link' });

            // Aplica via Delta EXPLÍCITO (com o atributo link embutido), sob a
            // blindagem de seleção — não depende de foco/seleção do Quill, que
            // quebraria nesse estado.
            setTimeout(() => {
                const Delta = window.Quill.import('delta');
                if (this.guardSelection) this.guardSelection();
                try {
                    if (range.length > 0) {
                        // Aplica o link ao texto já selecionado.
                        this.quill.updateContents(
                            new Delta().retain(range.index).retain(range.length, { link: url }),
                            'user',
                        );
                        this.savedRange = { index: range.index + range.length, length: 0 };
                    } else {
                        // Sem seleção: insere o texto (ou a própria URL) como link.
                        const display = text || url;
                        this.quill.updateContents(
                            new Delta().retain(range.index).insert(display, { link: url }),
                            'user',
                        );
                        this.savedRange = { index: range.index + display.length, length: 0 };
                    }
                } catch (e) {
                    console.warn('insertLink fallback:', e?.message);
                } finally {
                    if (this.unguardSelection) setTimeout(this.unguardSelection, 200);
                }
                this.syncToLivewire();
            }, 60);
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

                // Validação de tamanho ANTES do upload: evita o erro 413 do
                // servidor e mostra uma mensagem clara. Limite vindo do servidor
                // (config/editor.php). Para vídeos maiores, o usuário usa um link.
                const maxBytes = this.maxUploadKb * 1024;
                const limitMb = Math.round(this.maxUploadKb / 1024);
                if (file.size > maxBytes) {
                    const mb = Math.round(file.size / (1024 * 1024));
                    this.showError(
                        type === 'video'
                            ? `Este vídeo tem ${mb} MB e excede o limite de ${limitMb} MB. Para vídeos maiores, cole um link do YouTube ou Vimeo no botão de vídeo.`
                            : `Esta imagem tem ${mb} MB e excede o limite de ${limitMb} MB. Reduza o tamanho e tente novamente.`,
                    );
                    return;
                }

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

                    if (!response.ok) {
                        if (response.status === 413 || response.status === 422) {
                            this.showError(
                                `O arquivo excede o tamanho permitido (máx. ${limitMb} MB). Para vídeos maiores, use um link do YouTube ou Vimeo.`,
                            );
                        } else {
                            this.showError('Não foi possível enviar o arquivo. Verifique o formato e tente novamente.');
                        }
                        return;
                    }

                    const data = await response.json();
                    const embedType = data.type === 'video' ? 'video' : 'image';

                    this.insertEmbedAt(embedType, data.url);
                } catch (error) {
                    console.error('Upload Error:', error);
                    this.showError('Não foi possível enviar o arquivo. Verifique sua conexão e tente novamente.');
                } finally {
                    this.uploading = false;
                }
            };

            inputEl.click();
        },

        /** Exibe uma mensagem de erro no modal (substitui o alert nativo). */
        showError(message) {
            this.errorMessage = message;
            this.$dispatch('open-modal', { name: 'quill-error' });
        },
    }));
});
