/**
 * Modal component that works like a popin dialog.
 * Pure TypeScript replacement for the Dojo-based customgame.modal.
 * Uses CSS transitions for animation instead of Dojo FX.
 *
 * Styling reference (add to your SCSS):
 * @see src/scss/modals.scss
 */

/** Possible close behaviours when clicking the close icon or underlay */
export enum CloseAction {
    Destroy = 'destroy',
    Hide = 'hide',
}

/** Modal lifecycle states */
enum ModalState {
    Hidden = 'hidden',
    Opening = 'opening',
    Visible = 'visible',
    Closing = 'closing',
    Destroyed = 'destroyed',
}

export interface ModalConfig {
    container?: string | HTMLElement;
    class?: string;
    autoShow?: boolean;

    closeIcon?: string | null;
    closeAction?: CloseAction;
    closeWhenClickOnUnderlay?: boolean;

    helpIcon?: string | null;
    helpLink?: string;

    title?: string | null;
    contents?: string;

    verticalAlign?: 'center' | 'top';

    animationDuration?: number;

    fadeIn?: boolean;
    fadeOut?: boolean;

    openAnimation?: boolean;
    openAnimationTarget?: string | HTMLElement | null;
    openAnimationDelta?: number;

    onShow?: (() => void) | null;
    onHide?: (() => void) | null;

    statusElt?: string | HTMLElement | null;

    scale?: number;
    breakpoint?: number | null;
}

const DEFAULT_CONFIG: Required<ModalConfig> = {
    container: 'ebd-body',
    class: 'custom_popin',
    autoShow: false,

    closeIcon: 'fa-times-circle',
    closeAction: CloseAction.Destroy,
    closeWhenClickOnUnderlay: true,

    helpIcon: null,
    helpLink: '#',

    title: null,
    contents: '',

    verticalAlign: 'center',

    animationDuration: 500,

    fadeIn: true,
    fadeOut: true,

    openAnimation: false,
    openAnimationTarget: null,
    openAnimationDelta: 200,

    onShow: null,
    onHide: null,

    statusElt: null,

    scale: 1,
    breakpoint: null,
};

export class Modal {
    id: string;
    private config: Required<ModalConfig>;
    private state: ModalState = ModalState.Hidden;
    private containerEl: HTMLElement | null = null;
    private resizeObserver: ResizeObserver | null = null;
    private animationPromise: Promise<void> | null = null;
    private statusElt: HTMLElement | null = null;
    private openAnimationTargetEl: HTMLElement | null = null;

    constructor(id: string, config: ModalConfig = {}) {
        if (!id) {
            throw new Error('You need an ID to create a modal');
        }
        this.id = id;

        // Merge defaults
        this.config = { ...DEFAULT_CONFIG, ...config };

        // Resolve string references to elements
        this.containerEl =
            typeof this.config.container === 'string'
                ? document.getElementById(this.config.container) || document.getElementById('ebd-body')
                : this.config.container;

        this.statusElt =
            typeof this.config.statusElt === 'string' ? document.getElementById(this.config.statusElt) : this.config.statusElt;

        this.openAnimationTargetEl =
            typeof this.config.openAnimationTarget === 'string'
                ? document.getElementById(this.config.openAnimationTarget)
                : this.config.openAnimationTarget;

        this.create();
        if (this.config.autoShow) {
            this.show();
        }
    }

    /** Returns whether the modal is currently visible */
    isDisplayed(): boolean {
        return this.state === ModalState.Visible;
    }

    /** Returns whether the modal DOM has been created and not destroyed */
    isCreated(): boolean {
        return this.id != null && this.state !== ModalState.Destroyed;
    }

    // ----------------------------------------------------------------
    // DOM creation
    // ----------------------------------------------------------------

    private resolveTpl(tpl: string, vars: Record<string, string>): string {
        return tpl.replace(/\$\{([^}]+)\}/g, (_m, key: string) => vars[key.trim()] ?? '');
    }

    private create(): void {
        // Destroy any existing container for this id
        const existing = document.getElementById(`popin_${this.id}_container`);
        if (existing) existing.remove();

        const cfg = this.config;
        const closeIconTpl =
            cfg.closeIcon == null
                ? ''
                : `<a href="#" id="popin_${this.id}_close" class="${cfg.class}_closeicon"><i class="fa ${cfg.closeIcon} fa-2x" aria-hidden="true"></i></a>`;
        const helpIconTpl =
            cfg.helpIcon == null
                ? ''
                : `<a href="${cfg.helpLink}" target="_blank" id="popin_${this.id}_help" class="${cfg.class}_helpicon"><i class="fa ${cfg.helpIcon} fa-2x" aria-hidden="true"></i></a>`;
        const titleTpl = cfg.title == null ? '' : `<h2 id="popin_${this.id}_title" class="${cfg.class}_title">${cfg.title}</h2>`;
        const contentsTpl = `<div id="popin_${this.id}_contents" class="${cfg.class}_contents">${cfg.contents}</div>`;

        const modalHtml = `
<div id="popin_${this.id}_container" class="${cfg.class}_container">
  <div id="popin_${this.id}_underlay" class="${cfg.class}_underlay"></div>
  <div id="popin_${this.id}_wrapper" class="${cfg.class}_wrapper">
    <div id="popin_${this.id}" class="${cfg.class}">
      ${titleTpl}
      ${closeIconTpl}
      ${helpIconTpl}
      ${contentsTpl}
    </div>
  </div>
</div>`;

        this.containerEl?.insertAdjacentHTML('beforeend', modalHtml);

        // Apply base styles via CSSOM
        const container = this.getEl('_container');
        const underlay = this.getEl('_underlay');
        const wrapper = this.getEl('_wrapper');

        if (container) {
            container.style.display = 'none';
            container.style.position = 'absolute';
            container.style.left = '0px';
            container.style.top = '0px';
            container.style.width = '100%';
            container.style.height = '100%';
        }
        if (underlay) {
            underlay.style.position = 'absolute';
            underlay.style.left = '0px';
            underlay.style.top = '0px';
            underlay.style.width = '100%';
            underlay.style.height = '100%';
            underlay.style.zIndex = '1049';
            underlay.style.opacity = '0';
            underlay.style.backgroundColor = 'white';
        }
        if (wrapper) {
            wrapper.style.position = 'absolute';
            wrapper.style.left = '0px';
            wrapper.style.top = '0px';
            wrapper.style.width = 'min(100%,100vw)';
            wrapper.style.height = '100vh';
            wrapper.style.zIndex = '1050';
            wrapper.style.opacity = '0';
            wrapper.style.display = 'flex';
            wrapper.style.justifyContent = 'center';
            wrapper.style.alignItems = cfg.verticalAlign === 'center' ? 'center' : 'flex-start';
            wrapper.style.paddingTop = cfg.verticalAlign === 'center' ? '0' : '125px';
            wrapper.style.transformOrigin = 'top left';
            // Prepare for CSS transitions
            wrapper.style.transition = `opacity ${cfg.animationDuration}ms ease`;
        }

        this.adjustSize();

        // Observe container resize
        if (container && this.containerEl) {
            this.resizeObserver = new ResizeObserver(() => this.adjustSize());
            this.resizeObserver.observe(this.containerEl);
        }

        // Attach event listeners
        this.getEl('_close')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.performClose();
        });

        if (cfg.closeWhenClickOnUnderlay) {
            this.getEl('_underlay')?.addEventListener('click', () => this.performClose());
            this.getEl('_wrapper')?.addEventListener('click', () => this.performClose());
        }

        // Stop propagation on the modal panel itself
        const popin = this.getEl('');
        if (popin) {
            popin.addEventListener('click', (e: MouseEvent) => e.stopPropagation());
        }
    }

    private performClose(): void {
        if (this.config.closeAction === CloseAction.Destroy) {
            this.destroy();
        } else {
            this.hide();
        }
    }

    private getEl(suffix: string): HTMLElement | null {
        if (this.state === ModalState.Destroyed) return null;
        const id = suffix ? `popin_${this.id}${suffix}` : `popin_${this.id}`;
        return document.getElementById(id);
    }

    // ----------------------------------------------------------------
    // Sizing
    // ----------------------------------------------------------------

    private adjustSize(): void {
        if (this.state === ModalState.Destroyed) return;
        const container = this.getEl('_container');
        const popin = this.getEl('');
        if (!container || !this.containerEl || !popin) return;

        const rect = this.containerEl.getBoundingClientRect();
        container.style.width = `${rect.width}px`;
        container.style.height = `${rect.height}px`;

        if (this.config.breakpoint != null) {
            const newModalWidth = rect.width * this.config.scale;
            let modalScale = newModalWidth / this.config.breakpoint;
            if (modalScale > 1) modalScale = 1;
            popin.style.transform = `scale(${modalScale})`;
            popin.style.transformOrigin = this.config.verticalAlign === 'center' ? 'center center' : 'top center';
        }
    }

    // ----------------------------------------------------------------
    // Animation helpers
    // ----------------------------------------------------------------

    /**
     * Run a CSS-transition-based fade-in.
     * Returns a promise that resolves when the transition completes.
     */
    private fadeInAnimation(): Promise<void> {
        return new Promise((resolve) => {
            const wrapper = this.getEl('_wrapper');
            const underlay = this.getEl('_underlay');
            if (!wrapper || !underlay) {
                resolve();
                return;
            }

            const duration = this.config.fadeIn ? this.config.animationDuration : 0;

            // Set transition on wrapper
            const transforms: string[] = ['opacity'];
            const wrapperTransition = `opacity ${duration}ms ease`;
            wrapper.style.transition = wrapperTransition;

            // Underlay transition
            underlay.style.transition = `opacity ${duration}ms ease`;

            // Opening animation: also scale from a point
            if (this.config.openAnimation) {
                const pos = this.getOpeningTargetCenter();
                wrapper.style.transformOrigin = '0 0';
                wrapper.style.transform = `translate(${pos.x}px, ${pos.y}px) scale(0)`;
                wrapper.style.transition = `opacity ${duration}ms ease, transform ${duration + this.config.openAnimationDelta}ms ease`;
                // Force layout
                void wrapper.offsetHeight;
                wrapper.style.transform = `translate(0px, 0px) scale(1)`;
            }

            // Listen for transition end or just use timeout as fallback
            const onEnd = () => {
                wrapper.removeEventListener('transitionend', onEnd);
                resolve();
            };
            wrapper.addEventListener('transitionend', onEnd);

            // Show container
            const container = this.getEl('_container');
            if (container) container.style.display = 'block';

            // Animate
            void wrapper.offsetHeight; // force layout
            wrapper.style.opacity = '1';
            underlay.style.opacity = '0.7';

            // Fallback timeout
            if (duration === 0) {
                wrapper.removeEventListener('transitionend', onEnd);
                resolve();
            } else {
                setTimeout(() => {
                    wrapper.removeEventListener('transitionend', onEnd);
                    resolve();
                }, duration + 50);
            }
        });
    }

    private fadeOutAnimation(): Promise<void> {
        return new Promise((resolve) => {
            const wrapper = this.getEl('_wrapper');
            const underlay = this.getEl('_underlay');
            if (!wrapper || !underlay) {
                resolve();
                return;
            }

            const duration = this.config.fadeOut
                ? this.config.animationDuration + (this.config.openAnimation ? this.config.openAnimationDelta : 0)
                : 0;

            wrapper.style.transition = `opacity ${duration}ms ease`;
            underlay.style.transition = `opacity ${duration}ms ease`;

            // Closing animation
            if (this.config.openAnimation) {
                const pos = this.getOpeningTargetCenter();
                wrapper.style.transformOrigin = '0 0';
                wrapper.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
                void wrapper.offsetHeight;
                wrapper.style.transform = `translate(${pos.x}px, ${pos.y}px) scale(0)`;
            }

            const onEnd = () => {
                wrapper.removeEventListener('transitionend', onEnd);
                resolve();
            };
            wrapper.addEventListener('transitionend', onEnd);

            void wrapper.offsetHeight;
            wrapper.style.opacity = '0';
            underlay.style.opacity = '0';

            if (duration === 0) {
                wrapper.removeEventListener('transitionend', onEnd);
                resolve();
            } else {
                setTimeout(() => {
                    wrapper.removeEventListener('transitionend', onEnd);
                    resolve();
                }, duration + 50);
            }
        });
    }

    private getOpeningTargetCenter(): { x: number; y: number } {
        if (this.openAnimationTargetEl) {
            const rect = this.openAnimationTargetEl.getBoundingClientRect();
            return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
        }
        return {
            x: Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0) / 2,
            y: Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0) / 2,
        };
    }

    // ----------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------

    /** Show the modal with fade-in animation */
    async show(): Promise<void> {
        if (this.state === ModalState.Opening || this.state === ModalState.Visible) return;
        if (this.state === ModalState.Destroyed) return;

        this.state = ModalState.Opening;

        if (this.statusElt) {
            this.statusElt.classList.add('opened');
        }

        this.adjustSize();
        await this.fadeInAnimation();

        if (this.state !== ModalState.Opening) return;
        this.state = ModalState.Visible;

        this.config.onShow?.();
    }

    /** Hide the modal without destroying DOM */
    async hide(): Promise<void> {
        if (this.state === ModalState.Closing || this.state === ModalState.Hidden) return;
        if (this.state === ModalState.Destroyed) return;

        this.state = ModalState.Closing;

        await this.fadeOutAnimation();

        if (this.state !== ModalState.Closing) return;
        this.state = ModalState.Hidden;

        const container = this.getEl('_container');
        if (container) container.style.display = 'none';

        this.config.onHide?.();

        if (this.statusElt) {
            this.statusElt.classList.remove('opened');
        }
    }

    /** Hide and then fully remove DOM elements */
    async destroy(): Promise<void> {
        if (this.state === ModalState.Closing || this.state === ModalState.Destroyed) return;

        const wasHidden = this.state === ModalState.Hidden;
        this.state = ModalState.Closing;

        if (!wasHidden) {
            await this.fadeOutAnimation();
        }

        if (this.state !== ModalState.Closing) return;
        this.kill();
    }

    /** Immediately remove DOM elements without animation */
    private kill(): void {
        this.state = ModalState.Destroyed;

        const container = this.getEl('_container');
        if (container) container.remove();

        this.resizeObserver?.disconnect();
        this.resizeObserver = null;

        this.id = null as unknown as string; // Signal destroyed state via isCreated()

        if (this.statusElt) {
            this.statusElt.classList.remove('opened');
        }
    }
}
