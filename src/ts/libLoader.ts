/**
 * Lib loader for bga-animations and bga-dice.
 *
 * On the BGA platform, loads via the framework's importEsmLib().
 * In local testing, reads from window globals pre-loaded by misc/local/script.js.
 */

const isPlatform = typeof importEsmLib !== 'undefined';

let animationsModule: any = null;
let animationsPromise: Promise<any> | null = null;
let diceModule: any = null;
let dicePromise: Promise<any> | null = null;
let animationManagerInstance: any = null;

/**
 * Returns the bga-animations module (BgaAnimations namespace object).
 * Contains: BgaAnimations (namespace), Manager (AnimationManager class).
 */
export function useAnimations(): Promise<any> {
    if (animationsModule) return Promise.resolve(animationsModule);
    if (!animationsPromise) {
        if (isPlatform) {
            animationsPromise = importEsmLib('bga-animations', '1.x');
        } else {
            animationsPromise = Promise.resolve().then(() => {
                const mod = (window as any).__bgaAnimations;
                if (!mod)
                    throw new Error(
                        '[libLoader] bga-animations not found on window.__bgaAnimations. Did you load it in the local script?'
                    );
                return mod;
            });
        }
        animationsPromise = animationsPromise.then((mod) => {
            animationsModule = mod;
            return mod;
        });
    }
    return animationsPromise;
}

/**
 * Returns the bga-dice module (BgaDice namespace object).
 * Contains: BgaDice (namespace), Manager (DiceManager class), DiceStock, LineStock, SlotStock, VoidStock, sort.
 */
export function useDice(): Promise<any> {
    if (diceModule) return Promise.resolve(diceModule);
    if (!dicePromise) {
        if (isPlatform) {
            dicePromise = importEsmLib('bga-dice', '1.x');
        } else {
            dicePromise = Promise.resolve().then(() => {
                const mod = (window as any).__bgaDice;
                if (!mod)
                    throw new Error('[libLoader] bga-dice not found on window.__bgaDice. Did you load it in the local script?');
                return mod;
            });
        }
        dicePromise = dicePromise.then((mod) => {
            diceModule = mod;
            return mod;
        });
    }
    return dicePromise;
}

/**
 * Returns a shared AnimationManager instance (lazy singleton).
 * Created once from the bga-animations module and reused by all consumers.
 */
export async function getAnimationManager(): Promise<any> {
    if (!animationManagerInstance) {
        const BgaAnimations = await useAnimations();
        animationManagerInstance = new BgaAnimations.Manager({
            animationsActive: true,
        });
    }
    return animationManagerInstance;
}
