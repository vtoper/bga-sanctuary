import { getAnimationManager } from './libLoader';
import {
  addUpdatePlayerOrderingCallback,
  getCurrentPlayerId,
  attachRegisteredTooltips,
  addCustomTooltip,
  createDivElement,
  insertDivElement,
  slide,
} from './framework/utils';
import { formatIcon } from './format';

// Must stay in sync with ZooMap::createGrid dimensions in ZooMap.php
const ZOO_MAP_GRID_DIM = { x: 7, y: 4 };

// Slots printed on img/market.jpg, locations `pool-1` … `pool-6` — see Tiles::fillPool
const MARKET_SLOT_COUNT = 6;

/**
 * Market slot (1-based) of a tile located in `pool-<n>`, null when it is not in the market.
 */
function getPoolSlot(tile: SanctuaryTile): number | null {
  if (!tile.location?.startsWith('pool-')) {
    return null;
  }

  const slot = Number(tile.location.split('-')[1]);
  return Number.isInteger(slot) && slot >= 1 && slot <= MARKET_SLOT_COUNT ? slot : null;
}

function getTileType(tileId: string): string {
  const prefix = tileId.charAt(0).toUpperCase();
  switch (prefix) {
    case 'A': return 'animal';
    case 'B': return 'building';
    case 'P': return 'project';
    case 'O': return 'open-area';
    default: return 'unknown';
  }
}

export class Players {
  game: any;
  bga: ExtendedBga;
  gamedatas: SanctuaryGamedatas | null = null;
  private counters: Map<string, Counter> = new Map();

  init(gamedatas: SanctuaryGamedatas, game: any, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
    this.gamedatas = gamedatas;

    // Market at top — created first so it appears above player boards
    this.createTilePool();

    // Current player's board first (own line), then other players after
    const currentPlayerId = String(getCurrentPlayerId());
    const orderedPlayers = [
      ...Object.values(this.gamedatas.players).filter((p) => String(p.id) === currentPlayerId),
      ...Object.values(this.gamedatas.players).filter((p) => String(p.id) !== currentPlayerId),
    ];
    for (const player of orderedPlayers) {
      this.createPlayerBoard(player);
      this.setupPlayerPanel(player);
    }

    this.setupPlayersCounters(gamedatas);
    this.createPlayerHand();
  }

  private createTilePool() {
    const gamePlayArea = document.getElementById('game_play_area');
    if (!gamePlayArea) {
      return;
    }

    const poolNode = createDivElement('tile-pool', 'sanctuary-tile-pool');
    const slotsHtml = Array.from(
      { length: MARKET_SLOT_COUNT },
      (unused, index) => `<div id="pool-slot-${index + 1}" class="pool-slot" data-slot="${index + 1}"></div>`,
    ).join('');
    poolNode.insertAdjacentHTML('beforeend', `<div class="tile-pool-tiles" id="tile-pool-tiles">${slotsHtml}</div>`);
    // Prepend so market appears at top
    gamePlayArea.insertBefore(poolNode, gamePlayArea.firstChild);
    this.setTilePool(this.gamedatas.tiles ?? []);
  }

  /**
   * Fill the fixed market slots: each tile goes in the slot matching its `pool-<n>` location.
   * Slots left empty keep their place, so tiles never shift in the display.
   */
  setTilePool(tiles: SanctuaryTile[]) {
    for (let slot = 1; slot <= MARKET_SLOT_COUNT; slot++) {
      const slotNode = this.getPoolSlotNode(slot);
      if (slotNode) {
        slotNode.innerHTML = '';
      }
    }

    for (const tile of tiles) {
      const slot = getPoolSlot(tile);
      const slotNode = slot === null ? null : this.getPoolSlotNode(slot);
      if (!slotNode) {
        continue;
      }

      const node = this.createTileNode(`pool-tile-${tile.id}`, 'pool-tile', tile);
      slotNode.appendChild(node);
      addCustomTooltip(node, this.buildTileTooltipHtml(tile));
    }
  }

  getPoolSlotNode(slot: number): HTMLElement | null {
    return document.getElementById(`pool-slot-${slot}`);
  }

  getPoolTileNode(tileId: string): HTMLElement | null {
    return document.getElementById(`pool-tile-${tileId}`);
  }

  /**
   * Create the floating hand drawer for the current player, fixed to bottom of screen.
   */
  private createPlayerHand() {
    const currentPlayerId = getCurrentPlayerId();
    const player = this.gamedatas.players[currentPlayerId];
    if (!player) {
      return; // spectator
    }

    document.body.insertAdjacentHTML(
      'beforeend',
      `<div id="floating-hand-wrapper">
        <div id="floating-hand-button-container">
          <div id="floating-hand-button" title="${_('Hand')}">
            <div class="icon-hand">✋</div>
          </div>
        </div>
        <div id="player-hand-${player.id}" class="sanctuary-player-hand">
          <div class="player-hand-tiles" id="hand-tiles-${player.id}"></div>
        </div>
      </div>`,
    );

    document.getElementById('floating-hand-button')?.addEventListener('click', () => {
      document.getElementById('floating-hand-wrapper')?.toggleAttribute('data-open');
    });

    this.setHand(player.hand ?? []);
  }

  /**
   * Replace the content of the current player's hand with the given tiles.
   */
  setHand(tiles: SanctuaryTile[]) {
    const handTilesNode = document.getElementById(`hand-tiles-${getCurrentPlayerId()}`);
    if (!handTilesNode) {
      return;
    }

    handTilesNode.innerHTML = '';
    for (const tile of tiles) {
      const node = this.createHandTile(tile);
      handTilesNode.appendChild(node);
      addCustomTooltip(node, this.buildTileTooltipHtml(tile));
    }
  }

  private createHandTile(tile: SanctuaryTile): HTMLElement {
    return this.createTileNode(`hand-tile-${tile.id}`, 'hand-tile', tile);
  }

  /**
   * Append tiles to the current player's hand. Opponents' hands are not rendered,
   * only their hand counter is kept in sync.
   */
  addTilesToHand(playerId: string | number, tiles: SanctuaryTile[]) {
    const handTilesNode = document.getElementById(`hand-tiles-${playerId}`);
    if (handTilesNode) {
      for (const tile of tiles) {
        const node = this.createHandTile(tile);
        handTilesNode.appendChild(node);
        addCustomTooltip(node, this.buildTileTooltipHtml(tile));
      }
    }

    this.incHandCount(playerId, tiles.length);
  }

  /**
   * Move tiles from the market to a player's hand. The market slot is simply emptied,
   * so the tiles left in the display keep their position.
   */
  async takeTilesFromPool(playerId: string | number, tiles: SanctuaryTile[]) {
    const handTilesNode = document.getElementById(`hand-tiles-${playerId}`);

    for (const tile of tiles) {
      if (!this.getPoolTileNode(tile.id)) {
        continue;
      }

      if (!handTilesNode) {
        // Opponents have no visible hand: send the tile towards their player panel
        await slide(`pool-tile-${tile.id}`, `player_board_${playerId}`, { destroy: true });
        continue;
      }

      await slide(`pool-tile-${tile.id}`, handTilesNode.id);
      // The element is now in the hand: turn the pool tile into a hand tile
      const node = this.getPoolTileNode(tile.id);
      if (node) {
        node.id = `hand-tile-${tile.id}`;
        node.classList.replace('pool-tile', 'hand-tile');
      }
    }

    this.incHandCount(playerId, tiles.length);
  }

  removePoolTiles(tileIds: string[]) {
    for (const tileId of tileIds) {
      this.getPoolTileNode(tileId)?.remove();
    }
  }

  /**
   * A tile leaves the hand and lands on the player's map.
   * The current player sees their own hand tile fly to the cell; the other players' hands are
   * hidden, so for them the tile flies in from the top bar instead.
   */
  async playTileFromHand(playerId: string | number, tile: SanctuaryTile) {
    if (!tile) {
      return;
    }

    const cell = this.getMapCellNode(playerId, tile.x, tile.y);
    if (cell) {
      const handNode = this.getHandTileNode(tile.id);
      await (handNode ? slide(handNode.id, cell.id, { destroy: true }) : this.slideTileFromTopBar(tile, cell));
    }

    this.removeHandTiles(playerId, [tile.id]);
    this.incHandCount(playerId, -1);
    this.setTileOnBoard(playerId, tile);
  }

  /**
   * Fly a tile from the top bar to a map cell, for tiles the player never saw in a hand.
   *
   * The flying tile is fixed-positioned on the body rather than appended to the title bar:
   * both the framework and our own notification hooks rewrite the title innerHTML, which would
   * drop the node during the `await` before the animation even starts.
   */
  private async slideTileFromTopBar(tile: SanctuaryTile, cell: HTMLElement) {
    const origin = Players.getTopBarPosition();
    const flyingNode = this.createTileNode(`flying-tile-${tile.id}`, 'hand-tile', tile);
    flyingNode.style.position = 'fixed';
    flyingNode.style.zIndex = '1000';
    flyingNode.style.top = `${origin.top}px`;
    document.body.appendChild(flyingNode);
    flyingNode.style.left = `${origin.centerX - flyingNode.offsetWidth / 2}px`;

    await slide(flyingNode.id, cell.id, { destroy: true });
  }

  /**
   * Screen position the tiles played by the other players fly from. The title bar is not always
   * laid out (a hidden element measures 0×0, which would make the tile take off from the very
   * corner of the viewport), so fall back to the next candidate, then to the top of the screen.
   */
  private static getTopBarPosition(): { centerX: number; top: number } {
    for (const id of ['pagemaintitletext', 'maintitlebar_content', 'page-title']) {
      const rect = document.getElementById(id)?.getBoundingClientRect();
      if (rect?.width) {
        return { centerX: rect.left + rect.width / 2, top: rect.top };
      }
    }

    return { centerX: window.innerWidth / 2, top: 0 };
  }

  /**
   * Clear the map cell holding a given tile (a release project covering an existing tile).
   */
  removeTileFromBoard(playerId: string | number, tileId: string) {
    const cell = document.querySelector(`#zoo-board-${playerId} .zoo-map-cell[data-id='${tileId}']`);
    if (!cell) {
      return;
    }

    this.clearMapCell(playerId, Number((cell as HTMLElement).dataset.x), Number((cell as HTMLElement).dataset.y));
  }

  getHandTileIds(): string[] {
    const handTilesNode = document.getElementById(`hand-tiles-${getCurrentPlayerId()}`);
    if (!handTilesNode) {
      return [];
    }

    return Array.from(handTilesNode.children).map((node) => (node as HTMLElement).dataset.id);
  }

  getHandTileNode(tileId: string): HTMLElement | null {
    return document.getElementById(`hand-tile-${tileId}`);
  }

  removeHandTiles(playerId: string | number, tileIds: string[]) {
    if (`${playerId}` !== `${getCurrentPlayerId()}`) {
      return;
    }

    for (const tileId of tileIds) {
      this.getHandTileNode(tileId)?.remove();
    }
  }

  setPouch(playerId: string | number, pouch: number) {
    const counter = this.counters.get(`${playerId}-pouch`);
    if (counter) {
      counter.toValue(pouch);
    }

    const player = this.gamedatas?.players[playerId];
    if (player) {
      player.pouch = pouch;
    }
  }

  setEnergy(playerId: string | number, energy: number) {
    const counter = this.counters.get(`${playerId}-energy`);
    if (counter) {
      counter.toValue(energy);
    }
  }

  setHandCount(playerId: string | number, handCount: number) {
    const counter = this.counters.get(`${playerId}-handCount`);
    if (counter) {
      counter.toValue(handCount);
    }
  }

  incHandCount(playerId: string | number, delta: number) {
    const counter = this.counters.get(`${playerId}-handCount`);
    if (counter) {
      counter.incValue(delta);
    }
  }

  /**
   * Re-render everything from a fresh getAllDatas payload (sent by `refreshUI` after an undo).
   */
  refreshUI(data: SanctuaryGamedatas) {
    if (!this.gamedatas) {
      return;
    }

    this.gamedatas.players = data.players;
    this.gamedatas.tiles = data.tiles ?? [];

    this.setTilePool(this.gamedatas.tiles);

    for (const playerId in this.gamedatas.players) {
      const player = this.gamedatas.players[playerId];
      this.setEnergy(playerId, player.energy ?? 0);
      this.setHandCount(playerId, player.handCount ?? 0);
      this.setPouch(playerId, player.pouch ?? 0);
      this.setActionCards(playerId, player.actionCards ?? []);
      this.setIcons(playerId, player.icons ?? {});
      this.clearPlayerMap(playerId);
      this.setBoardTiles(playerId);
    }

    const currentPlayer = this.gamedatas.players[getCurrentPlayerId()];
    if (currentPlayer) {
      this.setHand(currentPlayer.hand ?? []);
    }
  }

  private clearPlayerMap(playerId: string | number) {
    document.querySelectorAll(`#zoo-board-${playerId} .zoo-map-cell`).forEach((node) => {
      const cell = node as HTMLElement;
      cell.classList.remove('has-tile', 'tile-animal', 'tile-building', 'tile-project', 'tile-open-area', 'tile-unknown');
      delete cell.dataset.id;
    });
  }

  getMapCellNode(playerId: string | number, x: number, y: number): HTMLElement | null {
    return document.getElementById(`zoo-map-cell-${playerId}-${x}_${y}`);
  }

  clearMapCell(playerId: string | number, x: number, y: number) {
    const cell = this.getMapCellNode(playerId, x, y);
    if (!cell) return;
    cell.classList.remove('has-tile');
    cell.classList.remove('tile-animal', 'tile-building', 'tile-project', 'tile-open-area', 'tile-unknown');
    delete cell.dataset.id;
  }

  /**
   * Tile names are not sent by the server: they are derived from the tile id, eg B101_OutbackArea_N => Outback Area.
   */
  private getTileName(tile: SanctuaryTile): string {
    const parts = tile.id.split('_');
    if (parts.length > 1) {
      parts.shift(); // numbering prefix, eg A001
    }
    if (parts.length > 1 && /^[MFN]$/.test(parts[parts.length - 1])) {
      parts.pop(); // gender suffix
    }

    return parts
      .join(' ')
      .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
      .trim();
  }

  /**
   * Create a standard tile element (hex) with image background and type class.
   * Used for hand tiles, pool tiles, and can be adapted for map cells.
   */
  private createTileNode(id: string, cssClass: string, tile: SanctuaryTile): HTMLElement {
    const tileType = getTileType(tile.id);
    const node = createDivElement(id, `${cssClass} tile-${tileType}`, { id: tile.id, tileId: tile.id });
    node.title = this.getTileName(tile);
    return node;
  }

  // TODO: populate with effect descriptions for each tile ID
  private static readonly TILE_EFFECTS: Record<string, string> = {
    'A001_Lion_M': 'Predator · Africa. Place in a Rock enclosure of size 3 or more.',
    'A002_Lion_F': 'Predator · Africa. Place in a Rock enclosure of size 3 or more.',
    'B101_OutbackArea_N': 'Australian themed area. Provides 2 Rock habitat cells for Australian animals.',
    'P071_Hydrologist_N': 'Expert. Immediate: gain 3 Water habitat cells on your map.',
  };

  private buildTileTooltipHtml(tile: SanctuaryTile): string {
    const tileType = getTileType(tile.id);
    const tileName = this.getTileName(tile);
    const effect = Players.TILE_EFFECTS[tile.id] ?? _('Effect coming soon');
    return `<div class="tile-tooltip">
      <div class="tile-tooltip-image tile-${tileType}" data-id="${tile.id}"></div>
      <div class="tile-tooltip-info">
        <div class="tile-tooltip-name">${tileName}</div>
        <div class="tile-tooltip-type">${tileType}</div>
        <div class="tile-tooltip-effect">${effect}</div>
      </div>
    </div>`;
  }

  // Icon groups matching PHP Icons::CONTINENTS_AND_TYPES_AND_HABITATS
  private static readonly ICON_GROUPS = [
    ['Africa', 'Europe', 'Asia', 'Americas', 'Australia'],
    ['Bird', 'Predator', 'Herbivore', 'Bear', 'Reptile', 'Primate', 'PettingZoo'],
    ['Rock', 'Water', 'Forest', 'Undefined'],
  ];

  /**
   * Inject counter HTML and icons-summary into the BGA player panel.
   */
  private setupPlayerPanel(player: SanctuaryPlayer) {
    const panelNode = document.getElementById(`player_board_${player.id}`);
    if (!panelNode) {
      return;
    }

    const iconRowsHtml = Players.ICON_GROUPS.map((group) => {
      const iconsHtml = group
        .map(
          (icon) =>
            `<div class="icon-counter empty" id="icon-${player.id}-${icon}" data-icon="${icon}">
              <span class="icon-badge" data-type="${icon}"></span>
              <span class="icon-count">0</span>
            </div>`,
        )
        .join('');
      return `<div class="icons-row">${iconsHtml}</div>`;
    }).join('');

    panelNode.insertAdjacentHTML(
      'beforeend',
      `<div class="sanctuary-player-info">
        <div class="sanctuary-counter-holder">
          <div class="sanctuary-icon icon-energy" id="counter-${player.id}-energy"></div>
        </div>
        <div class="sanctuary-counter-holder">
          <div class="sanctuary-icon icon-handcount" id="counter-${player.id}-handCount"></div>
        </div>
        <div class="sanctuary-counter-holder">
          <div class="sanctuary-icon icon-pouch" id="counter-${player.id}-pouch"></div>
        </div>
      </div>
      <div class="sanctuary-icons-summary">
        ${iconRowsHtml}
      </div>`,
    );

    // Populate initial icon counts from gamedatas
    if (player.icons) {
      this.setIcons(player.id, player.icons);
    }
  }

  setIcons(playerId: string | number, icons: { [iconName: string]: number }) {
    for (const [iconName, count] of Object.entries(icons)) {
      const el = document.getElementById(`icon-${playerId}-${iconName}`);
      if (!el) continue;
      const countEl = el.querySelector('.icon-count');
      if (countEl) countEl.textContent = `${count}`;
      el.classList.toggle('empty', count === 0);
    }
  }

  private setupPlayersCounters(gamedatas: SanctuaryGamedatas) {
    for (const playerId in gamedatas.players) {
      const player = gamedatas.players[playerId];
      const resources: Array<{ key: keyof SanctuaryPlayer; initial: number }> = [
        { key: 'energy', initial: player.energy ?? 0 },
        { key: 'handCount', initial: player.handCount ?? 0 },
        { key: 'pouch', initial: player.pouch ?? 0 },
      ];

      for (const { key, initial } of resources) {
        const elementId = `counter-${playerId}-${key}`;
        const el = document.getElementById(elementId);
        if (!el) continue;

        const counter = new ebg.counter();
        counter.create(elementId);
        counter.setValue(initial);
        this.counters.set(`${playerId}-${key}`, counter);
      }
    }
  }

  /**
   * Create the board (zoo map) of a player and insert it in the DOM.
   */
  private createPlayerBoard(player: SanctuaryPlayer) {
    const playerBoardsElement = document.getElementById('game_play_area');
    const boardNode = insertDivElement(playerBoardsElement, `player-board-${player.id}`, 'sanctuary-player-board');
    boardNode.insertAdjacentHTML(
      'beforeend',
      `<div class="zoo-map" id="zoo-map-${player.id}">
        <div class="zoo-map-header">
          <span class="zoo-map-player-name">${player.name}</span>
          <div class="pouch-marker" title="${_('Pouch markers')}">
            <span class="pouch-marker-label">${_('Pouch')}</span>
            <span class="pouch-marker-value" id="pouch-counter-${player.id}">${player.pouch}</span>
          </div>
        </div>
        <div class="zoo-map-board">
          <div class="zoo-board" id="zoo-board-${player.id}"></div>
        </div>
        <div class="action-bar" id="action-bar-${player.id}"></div>
        <div class="action-cards" id="action-cards-${player.id}"></div>
      </div>`,
    );

    this.renderZooMapGrid(player.id);
    this.setBoardTiles(player.id);
    this.setActionCards(player.id, player.actionCards ?? []);
  }

  setActionCards(playerId: string | number, actionCards: SanctuaryActionCard[]) {
    const actionCardsNode = document.getElementById(`action-cards-${playerId}`);
    if (!actionCardsNode) {
      return;
    }

    actionCardsNode.innerHTML = '';
    [...actionCards]
      .sort((first, second) => first.strength - second.strength)
      .forEach((card) => {
        const cardNode = createDivElement(`action-card-${playerId}-${card.id}`, 'action-card', {
          cardId: `${card.id}`,
          position: `${card.strength}`,
          type: card.type,
          level: `${card.level}`,
        });
        cardNode.classList.toggle('active', card.status == 1);
        cardNode.innerHTML = `<span class="action-card-strength">${Players.getCurrentStrength(card)}</span>`;
        actionCardsNode.appendChild(cardNode);
      });
  }

  /**
   * Slot the card acts at: an upgraded (level II) card acts one slot further than the one it sits in.
   */
  private static getCurrentStrength(card: SanctuaryActionCard): number {
    return card.level == 2 ? card.strength + 1 : card.strength;
  }

  getActionCardNode(playerId: string | number, cardId: number | string): HTMLElement | null {
    return document.getElementById(`action-card-${playerId}-${cardId}`);
  }

  /**
   * Status 1 marks the action card chosen for the current turn, 0 releases it.
   */
  setActionCardStatus(playerId: string | number, cardId: number | string, status: number) {
    this.getActionCardNode(playerId, cardId)?.classList.toggle('active', status == 1);
  }

  /**
   * A level II card shows the second row of the sprite and acts one slot further.
   */
  setActionCardLevel(playerId: string | number, cardId: number | string, level: number) {
    const cardNode = this.getActionCardNode(playerId, cardId);
    if (!cardNode) {
      return;
    }

    cardNode.dataset.level = `${level}`;
    const strength = Number(cardNode.dataset.position);
    const strengthNode = cardNode.querySelector('.action-card-strength');
    if (strengthNode) {
      strengthNode.textContent = `${level == 2 ? strength + 1 : strength}`;
    }
  }

  /**
   * Place on a player's zoo map the tiles sent in the gamedatas with the 'board' location.
   */
  private setBoardTiles(playerId: string) {
    const tiles = (this.gamedatas.tiles ?? []).filter((tile) => tile.location === 'board' && `${tile.pId}` === `${playerId}`);

    for (const tile of tiles) {
      this.setTileOnBoard(playerId, tile);
    }
  }

  setTileOnBoard(playerId: string | number, tile: SanctuaryTile) {
    const cell = this.getMapCellNode(playerId, tile.x, tile.y);
    if (!cell) {
      return;
    }

    const tileType = getTileType(tile.id);
    cell.classList.add('has-tile', `tile-${tileType}`);
    cell.dataset.id = tile.id;
    cell.innerText = '';
    addCustomTooltip(cell, this.buildTileTooltipHtml(tile));
  }

  /**
   * Generate the hex cells of a player's zoo map, mirroring ZooMap::createGrid in ZooMap.php.
   */
  private renderZooMapGrid(playerId: string) {
    const zooBoard = document.getElementById(`zoo-board-${playerId}`);
    if (!zooBoard) {
      return;
    }

    for (let x = 0; x < ZOO_MAP_GRID_DIM.x; x++) {
      const size = ZOO_MAP_GRID_DIM.y - (x % 2 === 0 ? 1 : 0);
      for (let y = 0; y < size; y++) {
        const row = 2 * y + (x % 2 === 0 ? 1 : 0);
        const cell = createDivElement(`zoo-map-cell-${playerId}-${x}_${row}`, 'zoo-map-cell', {
          x: `${x}`,
          y: `${row}`,
        });
        cell.style.setProperty('grid-column', `${3 * x + 1} / span 4`);
        cell.style.setProperty('grid-row', `${row + 1} / span 2`);
        zooBoard.appendChild(cell);
      }
    }
  }
}

export const players = new Players();
