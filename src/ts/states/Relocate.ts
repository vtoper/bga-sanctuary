import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, getCurrentPlayerId, performAction } from '../framework/utils';

export class Relocate {
  game: Game;
  bga: ExtendedBga;

  private args: RelocateArgs | null = null;
  private selectedTileId: string | null = null;
  private selectedLocation: string | null = null;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  /**
   * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
   */
  onEnteringState(args: RelocateArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    this.resetSelection();

    if (!isCurrentPlayerActive) {
      return;
    }

    this.refresh();
  }

  /**
   * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
   */
  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    this.resetSelection();
    clearPossible();
  }

  private resetSelection() {
    this.selectedTileId = null;
    this.selectedLocation = null;
  }

  private refresh() {
    clearPossible();
    this.makeBoardTilesSelectable();
    this.makeLocationsSelectable();
    this.updateStatusBar();
  }

  private makeBoardTilesSelectable() {
    if (!this.args) {
      return;
    }

    const playerId = getCurrentPlayerId();

    // Get all tiles on the board
    this.args.playableCardsIds.forEach((tileId) => {
      const node = document.querySelector(`div[data-tile-id="${tileId}"]`);
      if (!node) {
        return;
      }

      if (tileId === this.selectedTileId) {
        node.classList.add('selected');
        onClick(node, () => this.selectTile(null));
      } else {
        onClick(node, () => this.selectTile(tileId));
      }
    });
  }

  private makeLocationsSelectable() {
    if (!this.selectedTileId || !this.args) {
      return;
    }

    const playerId = getCurrentPlayerId();
    for (const cell of this.args.playableTiles[this.selectedTileId] ?? []) {
      const node = players.getMapCellNode(playerId, cell.x, cell.y);
      if (!node) {
        continue;
      }

      const locationKey = `${cell.x}_${cell.y}`;
      if (locationKey === this.selectedLocation) {
        node.classList.add('selected');
      }
      onClick(node, () => this.selectLocation(locationKey));
    }
  }

  private selectTile(tileId: string | null) {
    this.resetSelection();
    this.selectedTileId = tileId;
    this.refresh();
  }

  private selectLocation(locationKey: string) {
    this.selectedLocation = this.selectedLocation === locationKey ? null : locationKey;
    this.refresh();
  }

  private updateStatusBar() {
    this.bga.statusBar.removeActionButtons();

    let label = _('Select a tile to relocate');
    if (this.selectedTileId && !this.selectedLocation) {
      label = _('Select a location');
    } else if (this.selectedTileId && this.selectedLocation) {
      label = _('Confirm');
    }

    const ready = !!this.selectedTileId && !!this.selectedLocation;
    this.bga.statusBar.addActionButton(label, () => this.confirm(), { disabled: !ready });
  }

  private confirm() {
    performAction('actRelocate', {
      tileId: this.selectedTileId,
      location: this.selectedLocation,
    });
  }
}
