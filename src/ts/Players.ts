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
