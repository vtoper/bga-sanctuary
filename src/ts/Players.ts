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

export class Players {
  game: any;
  bga: ExtendedBga;
  gamedatas: SanctuaryGamedatas | null = null;
  private counters: Map<string, Counter> = new Map();

  init(gamedatas: SanctuaryGamedatas, game: any, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
    this.gamedatas = gamedatas;

    for (const playerId in this.gamedatas.players) {
      const player = this.gamedatas.players[playerId];
      this.createPlayerBoard(player);
    }

    this.createPlayerHand();
  }

  /**
   * Create the hand of the current player (hexagonal tiles), below the boards.
   */
  private createPlayerHand() {
    const currentPlayerId = getCurrentPlayerId();
    const player = this.gamedatas.players[currentPlayerId];
    if (!player) {
      return; // spectator
    }

    const gamePlayArea = document.getElementById('game_play_area');
    const handNode = insertDivElement(gamePlayArea, `player-hand-${player.id}`, 'sanctuary-player-hand');
    handNode.insertAdjacentHTML('beforeend', `<div class="player-hand-tiles" id="hand-tiles-${player.id}"></div>`);

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
    const node = createDivElement(`hand-tile-${tile.id}`, 'hand-tile', { id: tile.id });
    node.innerText = tile.id; //this.getTileName(tile);
    return node;
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

  getMapCellNode(playerId: string | number, x: number, y: number): HTMLElement | null {
    return document.getElementById(`zoo-map-cell-${playerId}-${x}_${y}`);
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
   * Create the board (zoo map) of a player and insert it in the DOM.
   */
  private createPlayerBoard(player: SanctuaryPlayer) {
    const playerBoardsElement = document.getElementById('game_play_area');
    const boardNode = insertDivElement(playerBoardsElement, `player-board-${player.id}`, 'sanctuary-player-board');
    boardNode.insertAdjacentHTML(
      'beforeend',
      `<div class="zoo-map" id="zoo-map-${player.id}">zoo-map-${player.id} ${player.name}
        <div class="zoo-map-board">
          <div class="zoo-board" id="zoo-board-${player.id}"></div>
        </div>
      </div>`,
    );

    this.renderZooMapGrid(player.id);
    this.setBoardTiles(player.id);
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

    cell.classList.add('has-tile');
    cell.dataset.tileId = tile.id;
    cell.innerText = tile.id;
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
        cell.innerText = `${x},${row}`;
        zooBoard.appendChild(cell);
      }
    }
  }
}

export const players = new Players();
