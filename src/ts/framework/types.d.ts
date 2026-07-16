interface ExtendedGameGui extends ExtendedGameGuiGeneric<ExtendedPlayer, ExtendedGamedatas<ExtendedPlayer>> {}

interface ExtendedGameGuiGeneric<P extends Player, G extends Gamedatas<P> = Gamedatas<P>> extends GameGui<P, G> {
    _notif_uid_to_log_id: object;
    _notif_uid_to_mobile_log_id: object;
    _last_notif?: LastNotif;
    cancelLogs: (notifIds: string[]) => void;
    onPlaceLogOnChannel: (notif: any) => void;
    updatePlayerOrdering: () => void;
    notifqueue: HTMLElement;
    statusBar: ExtendedStatusBar;
}

interface LastNotif {
    logId: number;
    mobileLogId: number;
    msg: Notif<GenericArguments>;
}

interface ExtendedStatusBar extends StatusBar {
    addAttachedActionButton(
        attachTo: HTMLElement,
        label: string,
        callback: Function,
        params?: {
            color?: 'primary' | 'secondary' | 'alert';
            id?: string;
            classes?: string | string[];
            destination?: HTMLElement;
            title?: string;
            disabled?: boolean;
            tooltip?: string;
            confirm?: string | (() => string | undefined | null);
            autoclick?: boolean | { abortSignal?: AbortSignal; pausable?: boolean };
        }
    ): HTMLButtonElement;
}

interface ExtendedBga extends ExtendedBgaGeneric<ExtendedPlayer, ExtendedGamedatas<ExtendedPlayer>> {}
interface ExtendedBgaGeneric<P extends Player, G extends Gamedatas<P> = Gamedatas<P>> extends Bga<P, G> {
    gameui: ExtendedGameGuiGeneric<P, G>;
    statusBar: ExtendedStatusBar;
}

interface ExtendedGamedatas<T extends ExtendedPlayer> extends Gamedatas<T> {
    bgaEnvironment?: string;
}

interface ExtendedPlayer extends Player {
    no: number;
}

interface Model<T extends string = string, L extends string = string> {
    id: number;
    location: L;
    state: number;
    type?: T;
    playerId?: number;
}

interface GenericArguments {
    previousEngineChoices?: number;
    previousSteps?: number[];
    automaticAction?: boolean;
    optionalAction?: boolean;
    customStateDescription?: StateDescription;
    anytimeActions: AnytimeAction[];
}

interface StateDescription {
    descriptionMyTurn: string;
    description: string;
}

interface AnytimeAction {
    description: Description;
    id: string;
}

interface ResolveChoiceArgs {
    choices: Choice[];
    allChoices: Choice[];
}

interface Choice {
    id: number;
    description: Description;
    args: any;
    optionalAction: boolean;
    automaticAction: boolean;
    independentAction: boolean;
    irreversibleAction: boolean;
    source: string;
    sourceId: number;
}

type Description =
    | string
    | {
          log: string;
          args: object;
      };

interface EngineNode {
    type: string;
    state: string;
    playerId: string;
    actionResolved: boolean;
    args: object;
    children: EngineNode[];
    actionResolutionArgs: object;
}

interface RefreshUIArgs extends GenericArguments {
    data: any;
}

interface ClearTurnArgs extends GenericArguments {
    playerId: number;
    notifIds: string[];
}

interface EngineShownArgs extends GenericArguments {
    engine: EngineNode;
}

interface SelectNConfig {
    elements: Record<string, HTMLElement>;
    n: number;
    autoConfirm: boolean;
    confirmText: string;
    confirmBtn: boolean;
    cancelText: string;
    cancelBtn: boolean;
    callback: (selected: string[], others: string[]) => void;
    updateCallback: ((selected: string[]) => void) | null;
    optional: boolean;
    canPass: boolean;
    passCallback: (() => void) | null;
    btnContainer: string;
    class: string;
}
