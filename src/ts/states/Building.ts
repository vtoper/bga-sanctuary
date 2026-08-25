import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, getCurrentPlayerId, performAction } from '../framework/utils';

export class Building {
  game: Game;
  bga: ExtendedBga;

  private args: BuildingArgs | null = null;
  private selectedTileId: string | null = null;
  private selectedLocation: string | null = null;
  private selectedOpenAreas: string[] = [];

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  /**
   * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
   */
  onEnteringState(args: BuildingArgs, isCurrentPlayerActive: boolean) {
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
    this.makeHandSelectable();
    this.makeLocationsSelectable();
    this.updateStatusBar();
  }

  private makeHandSelectable() {
    for (const tileId of players.getHandTileIds()) {
      const node = players.getHandTileNode(tileId);
      if (!node) {
        continue;
      }

      if (tileId === this.selectedTileId) {
        node.classList.add('selected');
        onClick(node, () => this.selectBuilding(null));
      } else if (this.args.playableTiles[tileId]) {
        onClick(node, () => this.selectBuilding(tileId));
      }
    }
  }

  private makeLocationsSelectable() {
    if (!this.selectedTileId) {
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

  private selectBuilding(tileId: string | null) {
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

    const required = 0;
    let label = _('Select a building');
    if (this.selectedTileId && !this.selectedLocation) {
      label = _('Select a location');
    } else if (this.selectedTileId && this.selectedOpenAreas.length < required) {
      label = `${_('Select the open areas')} (${this.selectedOpenAreas.length}/${required})`;
    } else if (this.selectedTileId) {
      label = _('Confirm');
    }

    const ready = !!this.selectedTileId && !!this.selectedLocation;
    this.bga.statusBar.addActionButton(label, () => this.confirm(), { disabled: !ready });
  }

  private confirm() {
    performAction('actBuilding', {
      tileId: this.selectedTileId,
      location: this.selectedLocation,
    });
  }
}
