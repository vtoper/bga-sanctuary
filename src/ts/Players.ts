import { getAnimationManager } from './libLoader';
import {
  addUpdatePlayerOrderingCallback,
  getCurrentPlayerId,
  attachRegisteredTooltips,
  createDivElement,
  insertDivElement,
} from './framework/utils';
import { formatIcon } from './format';

// Must stay in sync with ZooMap::createGrid dimensions in ZooMap.php
const ZOO_MAP_GRID_DIM = { x: 7, y: 4 };

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

    for (const playerId in this.gamedatas.players) {
      const player = this.gamedatas.players[playerId];
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
    poolNode.insertAdjacentHTML('beforeend', '<div class="tile-pool-tiles" id="tile-pool-tiles"></div>');
    // Prepend so market appears at top
    gamePlayArea.insertBefore(poolNode, gamePlayArea.firstChild);
    this.setTilePool(this.gamedatas.tiles ?? []);
  }

  setTilePool(tiles: SanctuaryTile[]) {
    const poolNode = document.getElementById('tile-pool-tiles');
    if (!poolNode) {
      return;
    }

    poolNode.innerHTML = '';
    tiles
      .filter((tile) => tile.location.startsWith('pool-'))
      .sort((first, second) => {
        const firstSlot = Number(first.location.split('-')[1]);
        const secondSlot = Number(second.location.split('-')[1]);
        return firstSlot - secondSlot;
      })
      .forEach((tile) => {
        poolNode.appendChild(this.createTileNode(`pool-tile-${tile.id}`, 'pool-tile', tile));
      });
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
      handTilesNode.appendChild(this.createHandTile(tile));
    }
  }

  private createHandTile(tile: SanctuaryTile): HTMLElement {
    return this.createTileNode(`hand-tile-${tile.id}`, 'hand-tile', tile);
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

  getMapCellNode(playerId: string | number, x: number, y: number): HTMLElement | null {
    return document.getElementById(`zoo-map-cell-${playerId}-${x}_${y}`);
  }

  clearMapCell(playerId: string | number, x: number, y: number) {
    const cell = this.getMapCellNode(playerId, x, y);
    if (!cell) return;
    cell.classList.remove('has-tile');
    cell.classList.remove('tile-animal', 'tile-building', 'tile-project', 'tile-open-area', 'tile-unknown');
    delete cell.dataset.tileId;
    cell.style.backgroundImage = '';
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
    node.style.backgroundImage = `url(img/tiles/${tile.id}.jpg)`;
    node.title = this.getTileName(tile);
    return node;
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
        });
        cardNode.innerHTML = `<span class="action-card-type">${card.type}</span><span class="action-card-position">${card.level == 2 ? card.strength + 1 : card.strength}</span>`;
        actionCardsNode.appendChild(cardNode);
      });
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
    cell.dataset.tileId = tile.id;
    cell.style.backgroundImage = `url(img/tiles/${tile.id}.jpg)`;
    cell.title = this.getTileName(tile);
    cell.innerText = '';
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
