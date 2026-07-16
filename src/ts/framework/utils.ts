import { attach, detachAll, onClick } from './event';

export let gamegui: ExtendedGameGuiGeneric<Player, Gamedatas> = null;
export let bga: ExtendedBga = null;
let gameTitlePanelElement: HTMLElement = null;
let gamePanelElement: HTMLElement = null;
let updatePlayerOrderingCallbacks: Array<() => void> = [];
let buttonContainers: { [key: string]: HTMLElement } = {};
const playerBoardsElement: HTMLElement = document.getElementById('player_boards');

/** Map player color hex → sprite color name */
export const getColorName = (hex: string): string => {
    const map: Record<string, string> = {
        '60aaa1': 'blue',
        cf4a1f: 'red',
        cc7f17: 'yellow',
        '85902b': 'green',
    };
    return map[hex.toLowerCase()] ?? hex;
};

let isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
export const debug: (message: string, data: any) => void = isDebug ? console.info.bind(console) : function () {};

let registeredCustomTooltips: { [key: string]: string } = {};
let customTooltipIdCounter = 0;
export const registerCustomTooltip = (html: string, id = null) => {
    id = id || 'tooltipable-' + customTooltipIdCounter++;
    registeredCustomTooltips[id] = html;
    return id;
};
export const attachRegisteredTooltips = () => {
    Object.keys(registeredCustomTooltips).forEach((id) => {
        if ($(id)) {
            addCustomTooltip($(id), registeredCustomTooltips[id]);
        }
    });
    registeredCustomTooltips = {};
};

let openingTooltip = null;
export const addCustomTooltip = (elt: HTMLElement, html: string) => {
    if (gamegui === null) {
        let tooltip = document.createElement('div');

        tooltip.setAttribute('role', 'tooltip');
        tooltip.setAttribute('inert', 'true');
        tooltip.innerHTML = html;

        elt.appendChild(tooltip);

        elt.addEventListener('mouseenter', (e) => {
            if (openingTooltip) clearTimeout(openingTooltip);
            const tooltip = (e.target as HTMLElement).querySelector('[role=tooltip]');
            openingTooltip = setTimeout(() => tooltip.classList.add('active'), 500);
        });
        elt.addEventListener('mouseleave', (e) => {
            const tooltip = (e.target as HTMLElement).querySelector('[role=tooltip]');
            tooltip.classList.remove('active');
            if (openingTooltip) clearTimeout(openingTooltip);
        });
    } else {
        if (elt.id === '') elt.id = 'tooltipable-' + customTooltipIdCounter++;
        gameui.addTooltipHtml(elt.id, html);
    }
};

export const getGameTitlePanel = () => {
    if (!gameTitlePanelElement) {
        gameTitlePanelElement = insertDivElement(playerBoardsElement, 'game-title-panel', 'player-board', null, 'afterbegin');
    }

    addUpdatePlayerOrderingCallback(() => {
        playerBoardsElement.insertBefore(gameTitlePanelElement, playerBoardsElement.firstElementChild);
    });

    return gameTitlePanelElement;
};

export const addUpdatePlayerOrderingCallback = (callback: () => void) => {
    updatePlayerOrderingCallbacks.push(callback);
};

export const updatePlayerOrdering = () => {
    updatePlayerOrderingCallbacks.forEach((callback) => callback());
};

export const getGamePanel = () => {
    if (!gamePanelElement) {
        gamePanelElement = insertDivElement(playerBoardsElement, 'game-panel', 'player-board');
    }
    return gamePanelElement;
};

export const initUtils = (bgaVal: ExtendedBga) => {
    bga = bgaVal;
    gamegui = bga.gameui;

    const persistantNode = createDivElement('generalactions-persistant');
    document.getElementById('generalactions').insertAdjacentElement('afterend', persistantNode);

    buttonContainers['persistant'] = persistantNode;

    const restartNode = createDivElement('generalactions-restart');
    persistantNode.insertAdjacentElement('afterend', restartNode);
    buttonContainers['restart'] = restartNode;
};

export const getActivePlayerId = () => {
    return bga.players.getActivePlayerId();
};

export const isCurrentPlayerActive = () => {
    return bga.players.isCurrentPlayerActive();
};

export const isSpectator = () => {
    return bga.players.isCurrentPlayerSpectator();
};

export const ifActivePlayer = (callback: Function) => {
    return (...args: any[]) => {
        if (isCurrentPlayerActive()) {
            return callback(...args);
        }
    };
};

export const ifAnyPlayer = (callback: Function) => {
    return (...args: any[]) => {
        if (!isSpectator()) {
            return callback(...args);
        }
    };
};

export const ifNonActivePlayer = (callback: Function) => {
    return (...args: any[]) => {
        if (!isCurrentPlayerActive()) {
            return callback(...args);
        }
    };
};

export const ifSpectator = (callback: Function) => {
    return (...args: any[]) => {
        if (isSpectator()) {
            return callback(...args);
        }
    };
};

export const chainCallbacks = (...callbacks: Function[]) => {
    return (...args: any[]) => {
        let context = null;
        for (const cb of callbacks) {
            context = cb(...args, context);
        }

        return context;
    };
};

export const getCurrentPlayerId = () => {
    return gamegui.player_id;
};

export const isCurrentPlayer = (playerId: number) => {
    return gamegui.player_id == playerId;
};

export const getRandomId = (prefix: string | null = null) => {
    const id = Math.random().toString(36).substring(7);
    return prefix ? `${prefix}_${id}` : id;
};

export const toCamelCase = (str: string) => {
    return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
};

export const iterateObject = (obj: object, callback: (key: any, value: any) => void) => {
    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            callback(key, obj[key]);
        }
    }
};

export const mapObject = (obj: object, callback: (key: any, value: any) => any) => {
    const newObj = {};
    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            newObj[key] = callback(key, obj[key]);
        }
    }

    return newObj;
};

export const filterObject = (obj: object, callback: (key: any, value: any) => boolean) => {
    const newObj = {};
    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            if (callback(key, obj[key])) {
                newObj[key] = obj[key];
            }
        }
    }

    return newObj;
};

export const iterateObjectAsync = async (obj: object, callback: (key: any, value: any) => Promise<void>) => {
    for (const key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
            await callback(key, obj[key]);
        }
    }
};

export const removePrefix = (prefix: string | null) => (str: string) => {
    if (!prefix) {
        const index = str.indexOf('_');
        if (index === -1) {
            return str;
        }
        return str.substring(index + 1);
    }

    return str.replace(prefix, '');
};

export const removePropertyPrefix = (obj: object, prefix: string | null) => {
    const newObj = {};
    iterateObject(obj, (key, value) => {
        newObj[removePrefix(prefix)(key)] = value;
    });

    return newObj;
};

export const getRandomNumberBetween = (min: number, max: number) => Math.floor(Math.random() * (max - min + 1) + min);

export const performAction = function (action: string, args: any, params: object = {}) {
    params = Object.assign({ lock: true, checkAction: true }, params);
    bga.actions.performAction(action, args, params);
};

export const showDialog = (id: string, title: string, html: string, maxWidth?: number, hideCloseIcon: boolean = true) => {
    const dialog = new ebg.popindialog();
    dialog.create(id);
    dialog.setTitle(title);
    maxWidth && dialog.setMaxWidth(maxWidth);

    dialog.setContent(html);
    dialog.show();

    hideCloseIcon && dialog.hideCloseIcon();

    document.getElementById('popin_score-dialog_underlay')?.addEventListener('click', () => {
        dialog.destroy();
    });

    return dialog;
};

export const getNumberOfPlayers = () => {
    return Object.values(gamegui.gamedatas.players).length;
};

export const getPlayers = () => {
    return gamegui.gamedatas.players;
};

export const getPlayerName = (playerId: string) => {
    return getPlayers()[playerId].name;
};

export const getPlayerColor = (playerId: string | number) => {
    return getPlayers()[playerId].color;
};

export const getPlayerNameWithColor = (playerId: string) => {
    return `<span class="player-name" style="color: #${getPlayers()[playerId].color}">${getPlayerName(playerId)}</span>`;
};

export const getPlayerAvatarUrl = (playerId: string, size: number = 184) => {
    if (![32, 50, 92, 184].includes(size)) {
        throw new Error(`Invalid avatar size: ${size}`);
    }

    const avatarNode = document.getElementById(`avatar_${playerId}`);

    if (null != avatarNode) {
        let smallAvatarURL = avatarNode.getAttribute('src');
        if (size != 32) {
            return smallAvatarURL.replace('_32.', `_${size}.`);
        }
        return smallAvatarURL;
    } else {
        return `https://x.boardgamearena.net/data/data/avatar/default_${size}.jpg`;
    }
};

export const getPlayerAvatar = (playerId: string, size: number = 184, includeName: boolean = false) => {
    //size can be 184, 92, 50, 32
    const avatarUrl = getPlayerAvatarUrl(playerId, size);

    const imgNode = createAnyElement('img', null, 'player-avatar');
    imgNode.setAttribute('src', avatarUrl);

    let avatarNode = imgNode;

    if (includeName) {
        avatarNode = createDivElement(null, 'player-avatar-with-name');
        avatarNode.appendChild(imgNode);
        insertElement(avatarNode, getPlayerNameWithColor(playerId));
    }

    return avatarNode;
};

export const toProperty = (key: any) => {
    return (obj: any) => obj[key];
};

export const arrayGroupBy = (arr: any[], callback: (el: any) => any) => {
    return arr.reduce((acc, el) => {
        const groupName = callback(el);
        if (!acc.hasOwnProperty(groupName)) {
            acc[groupName] = [];
        }

        acc[groupName].push(el);
        return acc;
    }, {});
};

export const setPlayerScore = (playerId: number, score: number, animate = true) => {
    animate && bga.playerPanels.getScoreCounter(playerId).toValue(score);
    !animate && bga.playerPanels.getScoreCounter(playerId).setValue(score);
};

export const increasePlayerScore = (playerId: number, delta: number, animate = true) => {
    const newScore = bga.playerPanels.getScoreCounter(playerId).getValue() + delta;

    setPlayerScore(playerId, newScore, animate);
};

export const disableScore = (playerId: number) => {
    bga.playerPanels.getScoreCounter(playerId).disable();
};

export const orderPlayersWithCurrentPlayerFirst = (original: ExtendedPlayer[]) => {
    const players = [...original];
    players.sort((a, b) => a.no - b.no);

    const currentPlayerIndex = players.findIndex((player) => +player.id === +getCurrentPlayerId());
    const toMoveBack = players.splice(0, currentPlayerIndex);
    players.push(...toMoveBack);

    return players;
};

export const addFakeId = (node: HTMLElement) => {
    const className = node.className;
    node.id = getRandomId(className);
    node.dataset['isFakeId'] = 'true';
    return node;
};

export const removeFakeId = (node: HTMLElement) => {
    if (node.dataset.isFakeId) {
        node.removeAttribute('id');
        delete node.dataset.isFakeId;
    }
    return node;
};

export const createAnyElement = (tagName: string, id?: string, className?: string, data?: { [key: string]: any }) => {
    const element = document.createElement(tagName);
    id ? (element.id = id) : (element.id = getRandomId(className));
    className && (element.className = className);

    if (data) {
        for (const key in data) {
            element.dataset[key] = data[key];
        }
    }

    return element;
};

export const createSpanElement = (id?: string, className?: string, data?: { [key: string]: any }) => {
    return createAnyElement('span', id, className, data);
};

export const createDivElement = (id?: string, className?: string, data?: { [key: string]: any }) => {
    return createAnyElement('div', id, className, data);
};

export const createGridDivItem = (value: Element | string, gridColumn: string, gridRow: string, className = '') => {
    const node = createDivElement(null, `grid-item ${className} `);
    node.style.setProperty('grid-column', gridColumn);
    node.style.setProperty('grid-row', gridRow);
    if (value instanceof Element) {
        node.appendChild(value);
    } else {
        node.innerHTML = value;
    }

    return node;
};

export const insertGridDivElement = (
    parent: HTMLElement,
    value: Element | string,
    gridColumn: string,
    gridRow: string,
    className = '',
    position: InsertPosition = 'beforeend'
) => {
    const div = createGridDivItem(value, gridColumn, gridRow, className);
    parent.insertAdjacentElement(position, div);
    return div;
};

export const insertDivElement = (
    parent: HTMLElement,
    id?: string,
    className?: string,
    data?: { [key: string]: string },
    position: InsertPosition = 'beforeend'
): HTMLElement => {
    const div = createDivElement(id, className, data);
    parent.insertAdjacentElement(position, div);
    return div;
};

export const createElement = (templateHtml: string): HTMLElement => {
    const template = document.createElement('template');
    template.innerHTML = templateHtml.trim();
    const nodes = template.content.childNodes.length;
    if (nodes !== 1) {
        throw new Error('CreateElement can accept only one root element');
    }

    const newNode = template.content.childNodes[0] as HTMLElement;

    if (!newNode.id) {
        newNode.id = getRandomId(newNode.className);
    }

    return newNode;
};

export const insertElement = (parent: HTMLElement, template: string, position: InsertPosition = 'beforeend') => {
    parent.insertAdjacentHTML(position, template.trim());

    let element: Element | null = null;
    switch (position) {
        case 'beforeend':
            element = parent.lastElementChild;
            break;
        case 'afterbegin':
            element = parent.firstElementChild;
            break;
        case 'beforebegin':
            element = parent.previousElementSibling;
            break;
        case 'afterend':
            element = parent.nextElementSibling;
        default:
            break;
    }

    if (!element.id) {
        element.id = getRandomId(element.className);
    }

    return element;
};

export const confirmationDialog = (message: string, callback: (result: boolean) => void) => {
    bga.dialogs.confirmation(message).then((result) => {
        callback(result);
    });
};

export const copySizeBaseProperty = (source: HTMLElement, target: HTMLElement, property: string) => {
    target.style.setProperty(property, getComputedStyle(source).getPropertyValue(property));
};

export const slugify = (str: string): string => {
    return str
        .replace(/^\s+|\s+$/g, '') // trim leading/trailing white space
        .toLowerCase() // convert string to lowercase
        .replace(/[^a-z0-9 -]/g, '') // remove any non-alphanumeric characters
        .replace(/\s+/g, '-') // replace spaces with hyphens
        .replace(/-+/g, '-'); // remove consecutive hyphens
};

export const isSlug = (str: string): boolean => {
    return str === slugify(str);
};

export let mainContainer = document.getElementById('game_play_area');
export let pageTitle = document.getElementById('page-title');
export const createMainContainer = () => {
    mainContainer = insertDivElement(document.getElementById('game_play_area'), 'game-content');
};
export const getPersistantActionButtonsNode = () => {
    return buttonContainers['persistant'];
};
export const clearPersistantActionButtonsNode = () => {
    const node = getPersistantActionButtonsNode();
    if (node) {
        node.innerHTML = '';
    }
};

export const getRestartActionButtonsNode = () => {
    return buttonContainers['restart'];
};

export const clearRestartActionButtonsNode = () => {
    const node = getRestartActionButtonsNode();
    if (node) {
        node.innerHTML = '';
    }
};

export const isNode = (node: any): node is Node => {
    return node instanceof Node;
};

export const clearPossible = () => {
    detachAll();
    document.querySelectorAll('.selected, .selectable, .unselectable').forEach((el) => {
        el.classList.remove('selected', 'selectable', 'unselectable');
    });
};

export const onSelectN = function (options: Partial<SelectNConfig>) {
    const config: SelectNConfig = {
        elements: {},
        n: 0,
        autoConfirm: false,
        confirmText: _('Confirm'),
        confirmBtn: true,
        cancelText: _('Cancel'),
        cancelBtn: true,
        callback: () => {},
        updateCallback: null,
        optional: false,
        canPass: false,
        passCallback: null,
        btnContainer: 'customActions',
        class: '',
        ...options,
    };

    const elemIds = Object.keys(config.elements);
    let selectedElements: string[] = [];

    const updateStatus = () => {
        document.getElementById('btnCancelChoice')?.remove();
        if (selectedElements.length > 0 && config.cancelBtn) {
            bga.statusBar.addActionButton(
                config.cancelText,
                () => {
                    selectedElements = [];
                    updateStatus();
                },
                {
                    id: 'btnCancelChoice',
                    color: 'secondary',
                    destination: $(config.btnContainer),
                }
            );
        }

        document.getElementById('btnConfirmChoice')?.remove();
        if (
            ((config.optional === false && selectedElements.length === config.n) ||
                (config.optional === true && selectedElements.length <= config.n)) &&
            config.confirmBtn
        ) {
            const otherElems = elemIds.filter((id) => !selectedElements.includes(id));
            if (config.autoConfirm) {
                config.callback(selectedElements, otherElems);
                return;
            } else {
                bga.statusBar.addActionButton(config.confirmText, () => config.callback(selectedElements, otherElems), {
                    id: 'btnConfirmChoice',
                    destination: $(config.btnContainer),
                });
            }
        }

        elemIds.forEach((id) => {
            const elt = config.elements[id];
            const selected = selectedElements.includes(id);
            elt.classList.toggle('selected', selected);
            if (config.class !== '') elt.classList.toggle(config.class, selected);
            elt.classList.toggle('selectable', selected || selectedElements.length < config.n);
        });

        if (config.updateCallback !== null) {
            config.updateCallback(selectedElements);
        }
    };

    document.getElementById('btnPass')?.remove();
    if (config.canPass && config.passCallback) {
        bga.statusBar.addActionButton(_('Pass action'), () => config.passCallback!(), {
            id: 'btnPass',
            destination: $(config.btnContainer),
        });
    }

    Object.keys(config.elements).forEach((id) => {
        const elt = config.elements[id];
        onClick(elt, () => {
            const index = selectedElements.findIndex((t) => t === id);
            if (index === -1) {
                if (selectedElements.length >= config.n) return;
                selectedElements.push(id);
            } else {
                selectedElements.splice(index, 1);
            }
            updateStatus();
        });
    });
};
